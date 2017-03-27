<?php
namespace App\ProtoControllers\Employe;

use \App\Models;
use App\Models\Planning\Creneau;
use App\ProtoControllers\Responsable;

/**
 * ProtoContrôleur abstrait d'heures, en attendant la migration vers le MVC REST
 *
 * TODO: On pourrait davantage faire de chose dans la classe abstraite, mais on est empêché par les log. Ça devrait être un sujet d'étude pour l'avenir
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
abstract class AHeure
{
    /**
     * Encapsule le comportement du formulaire d'édition d'heures
     *
     * @param int $id
     *
     * @return string
     * @access public
     */
    abstract public function getForm($id = NIL_INT);

    /**
     * Traite la demande/modification/suppression
     *
     * @param array  $post
     * @param array  &$errorsLst
     * @param string $notice
     *
     * @return int
     */
    protected function postHtmlCommon(array $post, array &$errorsLst, &$notice)
    {
        $user = $_SESSION['userlogin'];
        if (!empty($post['_METHOD'])) {
            switch ($post['_METHOD']) {
                case 'DELETE':
                    if (!$this->canUserDelete($post['id_heure'], $user)) {
                        return NIL_INT;
                    } else {
                        return $this->delete($post['id_heure'], $user, $errorsLst, $notice);
                    }

                    break;
                case 'PUT':
                    if (!$this->canUserEdit($post['id_heure'], $user)) {
                        return NIL_INT;
                    } else {
                        return $this->put($post, $errorsLst, $user);
                    }
                    break;
            }
        } else {
            return $this->post($post, $errorsLst, $user);
        }
    }

    /**
    * Créé une demande d'heures
    *
    * @param array  $post
    * @param array  &$errorsLst
    * @param string $user
    *
    * @return int
    */
    abstract protected function post(array $post, array &$errorsLst, $user);

    /**
     * Supprime une demande d'heures
     *
     * @param int    $id
     * @param string $user
     * @param array  &$errorsLst
     * @param string &$notice
     *
     * @return int
     */
    abstract protected function delete($id, $user, array &$errorsLst, &$notice);

    /**
     * Met à jour une demande d'heures
     *
     * @param array  $put
     * @param array  &$errorsLst
     * @param string $user
     *
     * @return int
     */
    abstract protected function put(array $put, array &$errorsLst, $user);

    /**
     * Contrôle l'éligibilité d'une demande d'heures
     *
     * @param array  $post
     * @param string $user
     * @param array  &$errorsLst
     * @param int    $id
     *
     * @return bool True s'il y a des erreurs
     */
    protected function hasErreurs(array $post, $user, array &$errorsLst, $id = NIL_INT)
    {
        $localErrors = [];

        /* Syntaxique : champs requis et format */
        if (empty($post['jour'])) {
            $localErrors['Jour'] = _('champ_necessaire');
        }
        if (empty($post['debut_heure'])) {
            $localErrors['Heure de début'] = _('champ_necessaire');
        } elseif (!\App\Helpers\Formatter::isHourFormat($post['debut_heure'])) {
            $localErrors['Heure de début'] = _('Format_heure_incorrect');
        }
        if (empty($post['fin_heure'])) {
            $localErrors['Heure de fin'] = _('champ_necessaire');
        } elseif (!\App\Helpers\Formatter::isHourFormat($post['fin_heure'])) {
            $localErrors['Heure de fin'] = _('Format_heure_incorrect');
        }
        if (!empty($localErrors)) {
            $errorsLst = array_merge($errorsLst, $localErrors);

            return empty($localErrors);
        }

        /* Sémantique : sens de prise d'heure */
        if (NIL_INT !== strnatcmp($post['debut_heure'], $post['fin_heure'])) {
            $localErrors['Heure de début / Heure de fin'] = _('verif_saisie_erreur_heure_fin_avant_debut');
        }
        $planningUser = \utilisateur\Fonctions::getUserPlanning($user);
        if (is_null($planningUser)) {
            $localErrors['Planning'] = _('aucun_planning_associe_utilisateur');
        } else {
            if ($this->isChevauchement($post['jour'], $post['debut_heure'], $post['fin_heure'], $_SESSION['userlogin'], $id)) {
                $localErrors['Cohérence'] = _('Chevauchement_heure_avec_existant');
            }
            $data = $this->dataModel2Db($post, $user);
            if (0 >= $data['duree']) {
                $localErrors['Durée'] = _('duree_nulle');
            }
        }

        $errorsLst = array_merge($errorsLst, $localErrors);

        return !empty($localErrors);
    }

    /**
     * Transforme les données du modèle pour les rendre compréhensibles par le stockage
     *
     * @param array  $post Données postées
     * @param string $user Le nom de l'utilisateur pour récupérer le planning (à terme en injection de dépendance)
     *
     * @return array
     */
    protected function dataModel2Db(array $post, $user)
    {
        $jour  = \App\Helpers\Formatter::dateFr2Iso($post['jour']);
        $debut = strtotime($jour . ' ' . $post['debut_heure']);
        $fin   = strtotime($jour . ' ' . $post['fin_heure']);
        $statut = !empty(Responsable::getResponsablesUtilisateur($user))
            ? Models\AHeure::STATUT_DEMANDE
            : Models\AHeure::STATUT_VALIDATION_FINALE
        ;
        $comment = \includes\SQL::quote($post['comment']);
        $planningUser = \utilisateur\Fonctions::getUserPlanning($user);
        if (is_null($planningUser)) {
            $duree = 0;
            $typePeriode = 0;
        } else {
            $duree = $this->countDuree($debut, $fin, $planningUser);
            $typePeriode = $this->getTypePeriode($debut, $fin, $planningUser);
        }

        return [
            'debut' => (int) $debut,
            'fin'   => (int) $fin,
            'duree' => (int) $duree,
            'typePeriode' => (int) $typePeriode,
            'comment' => $comment,
            'statut' => $statut,
        ];
    }

    /**
     * Compte la vraie durée entre le début et la fin
     *
     * @param int   $debut
     * @param int   $fin
     * @param array $planning
     *
     * @return int Nombre de secondes de la durée totale
     */
    abstract protected function countDuree($debut, $fin, array $planning);

    /**
     * Retourne le type de période de l'heure
     *
     * @param int   $debut
     * @param int   $fin
     * @param array $planning
     *
     * @return int Parmi ceux de \App\Models\Planning\Creneau::TYPE_PERIODE_*
     */
     protected function getTypePeriode($debut, $fin, array $planning)
     {
         /*
          * Comme pour le moment on ne peut prendre une heure que sur un jour,
          * on prend arbitrairement le début...
          */
         $numeroSemaine = date('W', $debut);
         $realWeekType  = \utilisateur\Fonctions::getRealWeekType($planning, $numeroSemaine);
         if (!isset($planning[$realWeekType])) {
             return 0;
         }
         $planningWeek = $planning[$realWeekType];
         $jourId = date('N', $debut);
         if (!isset($planningWeek[$jourId])) {
             return 0;
         }
         $planningJour = $planningWeek[$jourId];
         $horodateDebut = \App\Helpers\Formatter::hour2Time(date('H\:i', $debut));
         $horodateFin   = \App\Helpers\Formatter::hour2Time(date('H\:i', $fin));
         $debutMatin = false;

         if (isset($planningJour[Creneau::TYPE_PERIODE_MATIN])) {
             if (!isset($planningJour[Creneau::TYPE_PERIODE_APRES_MIDI])) {
                 return Creneau::TYPE_PERIODE_MATIN;
             }
             $planningMatin = $planningJour[Creneau::TYPE_PERIODE_MATIN];
             $dernierCreneauMatin = $planningMatin[count($planningMatin) - 1];
             $planningApresMidi = $planningJour[Creneau::TYPE_PERIODE_APRES_MIDI];
             $premierCreneauApresMidi = current($planningApresMidi);

             if ($horodateFin <= $dernierCreneauMatin[Creneau::TYPE_HEURE_FIN]) {
                 return Creneau::TYPE_PERIODE_MATIN;
             } elseif ($horodateDebut >= $premierCreneauApresMidi[Creneau::TYPE_HEURE_DEBUT]) {
                 return Creneau::TYPE_PERIODE_APRES_MIDI;
             }
             return Creneau::TYPE_PERIODE_MATIN_APRES_MIDI;
         }
         return Creneau::TYPE_PERIODE_APRES_MIDI;
     }
    /**
     * Vérifie que l'utilisateur a bien le droit d'éditer la ressource
     *
     * @param int    $id
     * @param string $user
     *
     * @return bool
     */
    abstract public function canUserEdit($id, $user);

    /**
     * Vérifie que l'utilisateur a bien le droit de supprimer la ressource
     *
     * @param int    $id
     * @param string $user
     *
     * @return bool
     */
    abstract public function canUserDelete($id, $user);

    /**
     * Liste des heures
     *
     * @return string
     */
    abstract public function getListe();

    /**
     * Y-a-t-il une recherche dans l'avion ?
     *
     * @param array $post
     *
     * @return bool
     */
    protected function isSearch(array $post)
    {
        return !empty($post['search']);
    }

    /**
     * Retourne le formulaire de recherche de la liste
     *
     * @param array $champs Champs de recherche (postés ou défaut)
     *
     * @return string
     */
    abstract protected function getFormulaireRecherche(array $champs);

    /**
     * Transforme les champs de recherche afin d'être compris par la bdd
     *
     * @param array $post
     *
     * @return array
     */
    protected function transformChampsRecherche(array $post)
    {
        $champs = [];
        $search = $post['search'];
        foreach ($search as $key => $value) {
            if ('annee' === $key) {
                $champs['timestampDebut'] = \utilisateur\Fonctions::getTimestampPremierJourAnnee($value);
                $champs['timestampFin'] = \utilisateur\Fonctions::getTimestampDernierJourAnnee($value);
            } else {
                if ($value !== "all") { // si la valeur est différent de "all" le paramètres est passé au champ pour la futur requête sql
                    $champs[$key] = (int) $value;
                }
            }
        }

        return $champs;
    }

    /**
     * Vérifie le chevauchement entre les heures demandées et l'existant
     *
     * @param string $jour
     * @param string $heureDebut
     * @param string $heureFin
     * @param string $user
     * @param int    $id
     *
     * @return bool
     */
    abstract protected function isChevauchement($jour, $heureDebut, $heureFin, $user, $id);

    /**
     * Vérifie le chevauchement entre les heures demandées et les heures additionnelles
     *
     * @param string $jour
     * @param string $heureDebut
     * @param string $heureFin
     * @param string $user
     * @param int    $id
     *
     * @return bool
     */
    protected function isChevauchementHeureAdditionnelle($jour, $heureDebut, $heureFin, $user, $id = NIL_INT)
    {
        $jour = \App\Helpers\Formatter::dateFr2Iso($jour);
        $timestampDebut = strtotime($jour . ' ' . $heureDebut);
        $timestampFin   = strtotime($jour . ' ' . $heureFin);
        $statuts = [
            Models\AHeure::STATUT_DEMANDE,
            Models\AHeure::STATUT_PREMIERE_VALIDATION,
            Models\AHeure::STATUT_VALIDATION_FINALE,
        ];

        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (SELECT statut
                FROM heure_additionnelle
                WHERE login = "' . $user . '"
                    AND statut IN (' . implode(',', $statuts) . ')
                    AND (debut <= ' . $timestampFin . ' AND fin >= ' . $timestampDebut . ')';
        if (NIL_INT !== $id) {
            $req .= ' AND id_heure !=' . $id;
        }
        $req .= ')';
        $queryAdd = $sql->query($req);

        return 0 < (int) $queryAdd->fetch_array()[0];
    }

    /**
     * Vérifie le chevauchement entre les heures demandées et les heures de repos
     *
     * @param string $jour
     * @param string $heureDebut
     * @param string $heureFin
     * @param string $user
     * @param int    $id
     *
     * @return bool
     */
    protected function isChevauchementHeureRepos($jour, $heureDebut, $heureFin, $user, $id = NIL_INT)
    {
        $jour = \App\Helpers\Formatter::dateFr2Iso($jour);
        $timestampDebut = strtotime($jour . ' ' . $heureDebut);
        $timestampFin   = strtotime($jour . ' ' . $heureFin);
        $statuts = [
            Models\AHeure::STATUT_DEMANDE,
            Models\AHeure::STATUT_PREMIERE_VALIDATION,
            Models\AHeure::STATUT_VALIDATION_FINALE,
        ];

        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (SELECT statut
                FROM heure_repos
                WHERE login = "' . $user . '"
                    AND statut IN (' . implode(',', $statuts) . ')
                    AND (debut <= ' . $timestampFin . ' AND fin >= ' . $timestampDebut . ')';
        if (NIL_INT !== $id) {
            $req .= ' AND id_heure !=' . $id;
        }
        $req .= ')';
        $queryRep = $sql->query($req);


        return 0 < (int) $queryRep->fetch_array()[0];
    }

    /**
     * Vérifie le chevauchement entre les heures demandées et les congés
     *
     * @param string $jour
     * @param string $heureDebut
     * @param string $heureFin
     * @param string $user
     *
     * @return bool
     */
    protected function isChevauchementConges($jour, $heureDebut, $heureFin, $user)
    {
        $jour = \App\Helpers\Formatter::dateFr2Iso($jour);
        $timestampDebut = strtotime($jour . ' ' . $heureDebut);
        $timestampFin   = strtotime($jour . ' ' . $heureFin);
        $planningUser = \utilisateur\Fonctions::getUserPlanning($user);
        if (!is_array($planningUser)) {
            return false;
        }
        $typePeriode = $this->getTypePeriode($timestampDebut, $timestampFin, $planningUser);
        if (!in_array($typePeriode, Creneau::getListeTypePeriode())) {
            return false;
        }
        $statuts = [
            Models\Conge::STATUT_DEMANDE,
            Models\Conge::STATUT_PREMIERE_VALIDATION,
            Models\Conge::STATUT_VALIDATION_FINALE,
        ];
        $where[] = '(p_date_deb < "' . $jour . '" AND p_date_fin > "' . $jour . '")';
        switch ($typePeriode) {
            case Creneau::TYPE_PERIODE_MATIN:
                $demiJournee = 'AND p_demi_jour_deb = "am"';
                break;
            case Creneau::TYPE_PERIODE_APRES_MIDI:
                $demiJournee = 'AND p_demi_jour_deb = "pm"';
                break;
            default:
                $demiJournee = '';
                break;
        }
        $where[] = '(p_date_deb = "' . $jour . '" ' . $demiJournee . ') OR (p_date_fin = "' . $jour . '" ' . $demiJournee . ')';

        // A DEPLACER
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
            SELECT p_num
            FROM conges_periode
            WHERE p_etat IN ("' . implode('","', $statuts) . '")
            AND (' . implode(' OR ', $where) . ')
        )';
        $queryConges = $sql->query($req);

        return 0 < (int) $queryConges->fetch_array()[0];
    }

    /*
     * SQL
     */

    /**
     * Ajoute une demande d'heures dans la BDD
     *
     * @param array  $data
     * @param string $user
     *
     * @return int
     */
    abstract protected function insert(array $data, $user);

    /**
     * Met à jour une demande d'heures dans la BDD
     *
     * @param array  $data
     * @param string $user
     * @param int    $id
     *
     * @return int
     */
    abstract protected function update(array $data, $user, $id);

    /**
     * Supprime une demande d'heures dans la BDD
     *
     * @param int $id
     * @param string $user
     *
     * @return int
     */
    abstract protected function deleteSQL($id, $user);

    /**
     * Retourne une liste d'id d'heures
     *
     * @param array $params Paramètres de recherche
     *
     * @return array
     */
    abstract public function getListeId(array $params);

    /**
     * Retourne une liste d'heures
     *
     * @param array $listId
     *
     * @return array
     */
    abstract public function getListeSQL(array $listId);
}
