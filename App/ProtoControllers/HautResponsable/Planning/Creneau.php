<?php
namespace App\ProtoControllers\HautResponsable\Planning;

use \App\Models\Planning\Creneau as ModelCreneau;

/**
 * ProtoContrôleur de créneau, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Creneau
{
    /**
     * Poste une liste de créneaux de planning
     *
     * @param array $post
     * @param int   $idPlanning
     * @param array &$errors
     *
     * @return int
     */
    public static function postCreneauxList(array $post, $idPlanning, array &$errors)
    {
        foreach ($post as $typeSemaine => $jours) {
            foreach ($jours as $jourId => $periodes) {
                if (!static::verifieCoherenceCreneaux($periodes, $errors)) {
                    return NIL_INT;
                }
            }
        }

        return static::insertCreneauList($post, $idPlanning);
    }

    /**
     * S'assure que les données passées aient un sens
     *
     * @param array $periodes
     * @param array &$errors
     *
     * @return bool
     */
    private static function verifieCoherenceCreneaux(array $periodes, array &$errors)
    {
        $localError = [];
        $precedentsCreneauxJours = [];
        $pattern = '/^(([01]?[0-9])|(2[0-3])):[0-5][0-9]$/';
        foreach ($periodes as $typeCreneau => $creneaux) {
            foreach ($creneaux as $creneauxJour) {
                $debut = $creneauxJour[ModelCreneau::TYPE_HEURE_DEBUT];
                $fin   = $creneauxJour[ModelCreneau::TYPE_HEURE_FIN];
                if (-1 !== strnatcmp($debut, $fin)) {
                    $localError['Créneaux de travail'][] = _('date_fin_superieure_date_debut');
                }
                if (!preg_match($pattern, $debut) || !preg_match($pattern, $fin))  {
                    $localError['Créneaux de travail'][] = _('Format_heure_incorrect');
                }
                if (!empty($precedentsCreneauxJours) && 1 !== strnatcmp($debut, $precedentsCreneauxJours[ModelCreneau::TYPE_HEURE_FIN])) {
                    $localError['Créneaux de travail'][] = _('creneaux_consecutifs');
                }
                $precedentsCreneauxJours = $creneauxJour;
            }
        }
        $errors = array_merge($errors, $localError);

        return empty($localError);
    }

    /**
     * Organise les Creneaux selon leurs critères de groupement à partir des infos de l'utilisateur
     *
     * @param array $list
     *
     * @return array
     */
    private static function groupCreneauxFromUser(array $list)
    {
        $grouped = [];
        foreach ($list as $jourId => $periodes) {
            foreach ($periodes as $typeCreneau => $creneaux) {
                foreach ($creneaux as $creneauxJour) {
                    $grouped[$jourId][$typeCreneau][] = [
                        ModelCreneau::TYPE_HEURE_DEBUT => $creneauxJour[ModelCreneau::TYPE_HEURE_DEBUT],
                        ModelCreneau::TYPE_HEURE_FIN => $creneauxJour[ModelCreneau::TYPE_HEURE_FIN]
                    ];
                }
            }
        }

        return $grouped;
    }

    /**
     * Organise les Creneaux selon leurs critères de groupement à partir des infos de la BDD
     *
     * @param array $creneaux
     *
     * @return array
     */
    private static function groupCreneauxFromDb(array $creneaux)
    {
        $grouped = [];
        foreach ($creneaux as $creneau) {
            $jourId      = $creneau['jour_id'];
            $typePeriode = $creneau['type_periode'];
            $debut       = \App\Helpers\Formatter::timestamp2Duree($creneau['debut']);
            $fin         = \App\Helpers\Formatter::timestamp2Duree($creneau['fin']);

            $grouped[$jourId][$typePeriode][] = [
                ModelCreneau::TYPE_HEURE_DEBUT => $debut,
                ModelCreneau::TYPE_HEURE_FIN   => $fin,
            ];
        }

        return $grouped;
    }

    /*
     * SQL
     *
     */

    /**
     * Supprime tous les créneaux d'un planning donné
     *
     * @param int $idPlanning
     *
     * @return bool
     */
    public static function deleteCreneauList($idPlanning)
    {
        $sql = \includes\SQL::singleton();
        $req = 'DELETE FROM planning_creneau WHERE planning_id = ' . (int) $idPlanning;
        $sql->query($req);

        return (bool) $sql->affected_rows;
    }

    /**
     * Insère une liste de créneaux de travail en base de donnée
     *
     * @param array $list
     * @param int   $idPlanning
     *
     * @return int
     */
    private static function insertCreneauList(array $list, $idPlanning)
    {
        $sql = \includes\SQL::singleton();
        $toInsert = [];
        foreach ($list as $typeSemaine => $jours) {
            foreach ($jours as $jourId => $periodes) {
                foreach ($periodes as $typeCreneau => $creneaux) {
                    foreach ($creneaux as $creneauxJour) {
                        $timeDebut = \App\Helpers\Formatter::hour2Time($creneauxJour[\App\Models\Planning\Creneau::TYPE_HEURE_DEBUT]);
                        $timeFin   = \App\Helpers\Formatter::hour2Time($creneauxJour[\App\Models\Planning\Creneau::TYPE_HEURE_FIN]);
                        $toInsert[] = '(null, ' . (int) $idPlanning . ', ' . (int) $jourId . ', ' . (int) $typeSemaine . ', ' . (int) $typeCreneau . ', "' . (int) $timeDebut . '", "' . (int) $timeFin . '")';
                    }
                }
            }
        }
        // insertion multiple en fonction de la liste
        $req = 'INSERT INTO planning_creneau (creneau_id, planning_id, jour_id, type_semaine, type_periode, debut, fin) VALUES ' . implode(', ', $toInsert);
        $query = $sql->query($req);

        return $sql->insert_id;
    }

    /**
     * Retourne les créneaux de travail groupés
     *
     * @param array $post        Totalité des données postées par l'utilisateur
     * @param int   $idPlanning
     * @param int   $typeSemaine
     *
     * @return array
     */
    public static function getCreneauxGroupes(array $post, $idPlanning, $typeSemaine)
    {
        if (!empty($post['creneaux'][$typeSemaine])) {
            return static::groupCreneauxFromUser($post['creneaux'][$typeSemaine]);
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM planning_creneau
                WHERE planning_id = ' . (int) $idPlanning . '
                  AND type_semaine = ' . (int) $typeSemaine;
        $res = $sql->query($req);
        if (!$res->num_rows) {
            return [];
        }

        return static::groupCreneauxFromDb($res->fetch_all(\MYSQLI_ASSOC));
    }
}
