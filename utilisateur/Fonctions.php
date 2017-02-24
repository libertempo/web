<?php

namespace utilisateur;

/**
 * Regroupement des fonctions liées à l'utilisateur
 */
class Fonctions
{

    /**
     * Retourne les options de select des années
     *
     * @return array
     */
    public static function getOptionsAnnees()
    {
        $current = date('Y');

        return [
            $current     => $current,
            $current - 1 => $current - 1,
            $current - 2 => $current - 2,
        ];
    }

    /**
     * Retourne le timestamp du dernier jour de l'année
     *
     * @param string $annee
     *
     * @return string
     */
    public static function getTimestampDernierJourAnnee($annee)
    {
        return mktime(23, 59, 59, 12, 31, $annee);
    }

    /**
     * Retourne le timestamp du premier jour de l'année
     *
     * @param string $annee
     *
     * @return string
     */
    public static function getTimestampPremierJourAnnee($annee)
    {
        return mktime(0, 0, 0, 1, 1, $annee);
    }

    
    
    /**
     * Retourne le planning de l'utilisateur organisé selon la hiérarchie habituelle
     * @example planningId[typeSemaine][jourId][typePeriode][creneaux]
     *
     * @param string $user
     *
     * @return ?array
     * @TODO $dataPlanning peut être nullable (php7.1)
     */
    public static function getUserPlanning($user)
    {
        $dataPlanning = null;
        $sql          = \includes\SQL::singleton();
        $reqUser      = 'SELECT planning.*
            FROM conges_users
                INNER JOIN planning USING (planning_id)
            WHERE u_login = "' . $sql->quote($user) . '"
                AND planning.status = ' . \App\Models\Planning::STATUS_ACTIVE;
        $queryUser = $sql->query($reqUser);
        $planning  = $queryUser->fetch_array();
        if (!empty($planning)) {
            $dataPlanning = [];
            $reqCreneau   = 'SELECT *
                FROM planning_creneau
                WHERE planning_id = ' . $planning['planning_id'];
            $queryCreneau = $sql->query($reqCreneau);

            while ($data = $queryCreneau->fetch_array()) {
                $dataPlanning[$data['type_semaine']][$data['jour_id']][$data['type_periode']][] = [
                    \App\Models\Planning\Creneau::TYPE_HEURE_DEBUT => $data['debut'],
                    \App\Models\Planning\Creneau::TYPE_HEURE_FIN   => $data['fin'],
                ];
            }
        }

        return $dataPlanning;
    }

    /**
     * Retourne le type de semaine applicable pour un planning et un numéro de semaine donnés
     *
     * @param array $planningUser
     * @param int   $weekOfDay
     *
     * @return int
     */
    public static function getRealWeekType(array $planningUser, $weekOfDay)
    {
        $typeSemaineDuJour = ($weekOfDay & 1)
        ? \App\Models\Planning\Creneau::TYPE_SEMAINE_IMPAIRE
        : \App\Models\Planning\Creneau::TYPE_SEMAINE_PAIRE;
        if (isset($planningUser[$typeSemaineDuJour])) {
            return $typeSemaineDuJour;
        } elseif (isset($planningUser[\App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE])) {
            return \App\Models\Planning\Creneau::TYPE_SEMAINE_COMMUNE;
        } else {
            return NIL_INT;
        }
    }

    /**
     * Vérifie que le jour est travaillé selon le planning
     *
     * @param array $planningWeek
     * @param int   $jourId
     */
    public static function isWorkingDay(array $planningWeek, $jourId)
    {
        return isset($planningWeek[$jourId]);
    }

    /**
     * Vérifie qu'une matinée est travaillée pour un jour de planning donné
     *
     * @param array $planningDay
     *
     * @return bool
     */
    public static function isWorkingMorning(array $planningDay)
    {
        return \utilisateur\Fonctions::isWorkingPeriodType($planningDay, \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN);
    }

    /**
     * Vérifie qu'une après midi est travaillée pour un jour de planning donné
     *
     * @param array $planningDay
     *
     * @return bool
     */
    public static function isWorkingAfternoon(array $planningDay)
    {
        return \utilisateur\Fonctions::isWorkingPeriodType($planningDay, \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI);
    }

    /**
     * Vérifie qu'un type de période est travaillé pour un jour de planning donné
     *
     * @param array $planningDay
     * @param int   $periodType
     *
     * @return bool
     */
    private static function isWorkingPeriodType(array $planningDay, $periodType)
    {
        return isset($planningDay[$periodType]);
    }

    /**
     * Retourne les jours de la semaine à désactiver dans datepicker
     *
     * @return array
     * @access public
     * @static
     */
    public static function getDatePickerDaysOfWeekDisabled()
    {
        $daysOfWeekDisabled = [];

        if (false == $_SESSION['config']['dimanche_travail']) {
            $daysOfWeekDisabled[] = 0;
        }
        if (false == $_SESSION['config']['samedi_travail']) {
            $daysOfWeekDisabled[] = 6;
        }
        return $daysOfWeekDisabled;
    }

    /**
     * Retourne les jours fériés à désactiver dans datepicker
     *
     * @return array
     * @access public
     * @static
     */
    public static function getDatePickerJoursFeries()
    {
        $Jferies = [];

        if (is_array($_SESSION["tab_j_feries"])) {
            foreach ($_SESSION["tab_j_feries"] as $date) {
                $Jferies[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }

        return $Jferies;
    }

    /**
     * Retourne les jours de fermeture à désactiver dans datepicker
     *
     * @return array
     * @access public
     * @static
     */
    public static function getDatePickerFermeture()
    {
        $Fermeture = [];

        if (isset($_SESSION["tab_j_fermeture"]) && is_array($_SESSION["tab_j_fermeture"])) {
            foreach ($_SESSION["tab_j_fermeture"] as $date) {
                $Fermeture[] = \App\Helpers\Formatter::dateIso2Fr($date);
            }
        }

        return $Fermeture;
    }

    /**
     * Retourne le jour de début du calendrier dans datepicker
     *
     * @return string
     * @access public
     * @static
     */
    public static function getDatePickerStartDate()
    {
        return ($_SESSION['config']['interdit_saisie_periode_date_passee']) ? 'd' : '';
    }

    // --------------------------------------

    /*
     * TODO: Où sont passées les heures validées (!= en cours donc) ?
     */

    public static function getOptionsTypeConges()
    {
        $options = [];
        $sql     = \includes\SQL::singleton();
        $req     = 'SELECT ta_libelle, ta_short_libelle
                FROM conges_type_absence';
        $res = $sql->query($req);

        while ($data = $res->fetch_array()) {
            $options[$data['ta_short_libelle']] = $data['ta_libelle'];
        }

        return $options;
    }
}
