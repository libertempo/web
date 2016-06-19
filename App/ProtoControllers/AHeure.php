<?php
namespace App\ProtoControllers;

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
    protected function post(array $post, array &$errorsLst, &$notice)
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
            if (!$this->hasErreurs($post, $user, $errorsLst)) {
                $data = $this->dataModel2Db($post, $user);
                $id   = $this->insert($data, $user);
                if (0 < $id) {
                    return $id;
                }
            }

            return NIL_INT;
        }
    }

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
        if ($this->isChevauchement($post['jour'], $post['debut_heure'], $post['fin_heure'], $id, $_SESSION['userlogin'])) {
            $localErrors['Cohérence'] = _('Chevauchement_heure_avec_existant');
        }
        $planningUser = \utilisateur\Fonctions::getUserPlanning($user);
        if (is_null($planningUser)) {
            $localErrors['Planning'] = _('aucun_planning_associe_utilisateur');
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
        $planningUser = \utilisateur\Fonctions::getUserPlanning($user);
        $duree = (is_null($planningUser)) ? 0 : $this->countDuree($debut,   $fin, $planningUser);

        return [
            'debut' => (int) $debut,
            'fin'   => (int) $fin,
            'duree' => (int) $duree,
        ];
    }

    /**
     * Compte la vraie durée entre le début et la fin
     *
     * @param int   $debut
     * @param int   $fin
     * @param array $planning
     *
     * @return int
     */
    abstract protected function countDuree($debut, $fin, array $planning);

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
                $champs[$key] = (int) $value;
            }
        }

        return $champs;
    }

    /*
     * SQL
     */

    /**
     * Vérifie le chevauchement entre les heures demandées et l'existant
     *
     * @param string $jour
     * @param string $heureDebut
     * @param string $heureFin
     * @param int    $id
     * @param string $user
     *
     * @return bool
     */
    abstract protected function isChevauchement($jour, $heureDebut, $heureFin, $id, $user);

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
    abstract protected function getListeId(array $params);

    /**
     * Retourne une liste d'heures
     *
     * @param array $listId
     *
     * @return array
     */
    abstract protected function getListeSQL(array $listId);
}
