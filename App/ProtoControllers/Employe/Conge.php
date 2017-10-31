<?php
namespace App\ProtoControllers\Employe;

use App\Models;
use App\Models\Planning\Creneau;

/**
 * ProtoContrôleur d'un congé, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Conge
{
    /**

     * Transforme les champs de recherche afin d'être compris par la bdd
     *
     * @param array $post
     *
     * @return array
     */
    public function transformChampsRecherche(array $post)
    {
        $champs = [];
        $search = $post['search'];
        foreach ($search as $key => $value) {
            if ('annee' === $key) {
                $champs['dateDebut'] = ((int) $value) . '-01-01';
                $champs['dateFin'] = ((int) $value) . '-12-31';
            } else {
                if ($value !== "all") {
                    // si la valeur est différent de tout le paramètres est passé au champ pour la futur requête sql
                    $champs[$key] = $value;
                }
            }
        }

        return $champs;
    }

    /*
     * SQL
     */

    /**
     * Retourne une liste d'id de congés
     *
     * @param array $params Paramètres de recherche
     *
     * @return array
     */
    public function getListeId(array $params)
    {
        $sql = \includes\SQL::singleton();
        if (!empty($params)) {
            $where = [];
            foreach ($params as $key => $value) {
                $value = $sql->quote($value);
                switch ($key) {
                    case 'dateDebut':
                        $where[] = 'p_date_deb >= "' . $value . '"';
                        break;
                    case 'dateFin':
                        $where[] = 'p_date_deb <= "' . $value . '"';
                        break;
                    case 'type':
                        $where[] = 'CTA.ta_short_libelle = "' . $value . '"';
                        break;
                    default:
                        $where[] = $key . ' = "' . $value . '"';
                        break;
                }
            }
        }
        $ids = [];
        $req = 'SELECT p_num AS id
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id) '
                . ((!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '');
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne une liste de congés
     *
     * @param array $listId
     *
     * @return array
     */
    public function getListeSQL(array $listId)
    {
        if (empty($listId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT CP.*, CTA.ta_libelle
                FROM conges_periode CP
                    INNER JOIN conges_type_absence CTA ON (CP.p_type = CTA.ta_id)
                WHERE p_num IN (' . implode(',', $listId) . ')
                ORDER BY p_date_deb DESC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Retourne les demandes d'un employé
     *
     */
    public static function getIdDemandesUtilisateur($user)
    {

        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT p_num AS id
                FROM conges_periode
                WHERE p_login = \'' . $sql->quote($user) . '\'
                AND p_etat = \'' . \App\Models\Conge::STATUT_DEMANDE . '\'';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Vérifie l'existence de congé basée sur les critères fournis
     *
     * @param array $params
     *
     * @return bool
     * @TODO: à terme, à baser sur le getList()
     */
    public function exists(array $params)
    {
        $sql = \includes\SQL::singleton();

        $where = [];
        foreach ($params as $key => $value) {
            $where[] = $key . ' = "' . $sql->quote($value) . '"';
        }
        $req = 'SELECT EXISTS (
                    SELECT *
                    FROM conges_periode
                    WHERE ' . implode(' AND ', $where) . '
        )';

        return 0 < (int) $sql->query($req)->fetch_array()[0];
    }

    /**
     * Vérifie le chevauchement des conges demandés et les heures,
     * additionnelles comme repos
     *
     * @param string $user Login de l'utilisateur
     * @param string $dateDebut Date au format YYYY-mm-dd
     * @param int $typeCreneauDebut Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $dateFin Date au format YYYY-mm-dd
     * @param int $typeCreneauFin Type de période parmi Creneau::TYPE_PERIODE_*
     *
     * @return bool
     */
    public function isChevauchement($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin)
    {
        return $this->isChevauchementHeuresRepos($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin)
        || $this->isChevauchementHeuresAdditionnelles($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin);
    }

    /**
     * Vérifie le chevauchement des conges demandés et les heures de repos
     *
     * @param string $user Login de l'utilisateur
     * @param string $dateDebut Date au format YYYY-mm-dd
     * @param int $typeCreneauDebut Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $dateFin Date au format YYYY-mm-dd
     * @param int $typeCreneauFin Type de période parmi Creneau::TYPE_PERIODE_*
     *
     * @return bool
     */
    private function isChevauchementHeuresRepos($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin)
    {
        return $this->isChevauchementHeures($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin, 'heure_repos');
    }

    /**
     * Vérifie le chevauchement des conges demandés et les heures additionnelles
     *
     * @param string $user Login de l'utilisateur
     * @param string $dateDebut Date au format YYYY-mm-dd
     * @param int $typeCreneauDebut Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $dateFin Date au format YYYY-mm-dd
     * @param int $typeCreneauFin Type de période parmi Creneau::TYPE_PERIODE_*
     *
     * @return bool
     */
    private function isChevauchementHeuresAdditionnelles($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin)
    {
        return $this->isChevauchementHeures($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin, 'heure_additionnelle');
    }

    /**
     * Vérifie le chevauchement des conges demandés et les heures, selon son type
     *
     * @param string $user Login de l'utilisateur
     * @param string $dateDebut Date au format YYYY-mm-dd
     * @param int $typeCreneauDebut Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $dateFin Date au format YYYY-mm-dd
     * @param int $typeCreneauFin Type de période parmi Creneau::TYPE_PERIODE_*
     * @param string $typeHeure Heure de repos ou additionnelle
     *
     * @return bool
     */
    private function isChevauchementHeures($user, $dateDebut, $typeCreneauDebut, $dateFin, $typeCreneauFin, $typeHeure)
    {
        $sql = \includes\SQL::singleton();
        $filtresDates[] = '(dateDebutHeure > "' . $dateDebut . '" AND dateDebutHeure < "' . $dateFin . '")';
        if (Creneau::TYPE_PERIODE_MATIN_APRES_MIDI === $typeCreneauDebut) {
            $filtresDates[] = '(dateDebutHeure = "' . $dateDebut . '")';
        } else {
            $filtresDates[] = '(dateDebutHeure = "' . $dateDebut . '" AND type_periode IN (' . $typeCreneauDebut . ',' . Creneau::TYPE_PERIODE_MATIN_APRES_MIDI . '))';
        }
        if (Creneau::TYPE_PERIODE_MATIN_APRES_MIDI === $typeCreneauFin) {
            $filtresDates[] = '(dateDebutHeure = "' . $dateFin . '")';
        } else {
            $filtresDates[] = '(dateDebutHeure = "' . $dateFin . '" AND type_periode IN (' . $typeCreneauFin . ',' . Creneau::TYPE_PERIODE_MATIN_APRES_MIDI . '))';
        }
        $etats = [
            Models\AHeure::STATUT_DEMANDE,
            Models\AHeure::STATUT_PREMIERE_VALIDATION,
            Models\AHeure::STATUT_VALIDATION_FINALE,
        ];

        $req = 'SELECT EXISTS (
            SELECT *
            FROM
                (SELECT *, DATE_FORMAT(FROM_UNIXTIME(debut), "%Y-%m-%d") AS dateDebutHeure
            FROM ' . $typeHeure . ') tmp
            WHERE statut IN ("' . implode('","', $etats) . '")
                AND login = "' . $sql->quote($user) . '"
                AND (' . implode(' OR ', $filtresDates) . ')
        )';
        $queryConges = $sql->query($req);

        return 0 < (int) $queryConges->fetch_array()[0];
    }
}
