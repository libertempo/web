<?php
namespace App\ProtoControllers\Ajax\Employe\Heure;

/**
 * ProtoContrôleur ajax d'heure de repos, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Repos
{
    /**
     * Retourne la liste des heures de repos satisfaisant aux critères fournis
     *
     * @param array $parametresRecherche Critères de filtres
     *
     * @return array utilisables par le calendrier
     */
    public function getListe(array $parametresRecherche)
    {
        $repos = [];
        $liste = $this->getListeSQL($this->getListeId($parametresRecherche));

        foreach ($liste as $heureRepos) {
            $repos[] = [
                'start' => date('c', $heureRepos['debut']),
                'end' => date('c', $heureRepos['fin']),
                'className' => 'heureRepos statut' . $heureRepos['statut'],

                'title' => 'Heure(s) de repos - ' . $heureRepos['u_prenom'] . ' ' . $heureRepos['u_nom'],
            ];
        }

        return $repos;
    }

    /*
     * SQL
     */


    /**
     * Retourne la liste des id d'heures de repos satisfaisant aux critères
     *
     * @param array $params
     *
     * @return array
     */
    private function getListeId(array $params)
    {
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_repos
                WHERE debut >= "' . strtotime($params['start']) . '"
                    AND debut <= "' . strtotime($params['end']) . '"
                    AND duree > 0
                    AND login IN ("' . implode('","', $params['users']) . '")
                    AND statut = ' . \App\Models\AHeure::STATUT_VALIDATION_FINALE;
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne une liste d'heures de repos en fonction de ses id
     *
     * @param array $listeId
     */
    private function getListeSQL(array $listeId)
    {
        if (empty($listeId)) {
            return [];
        }

        $listeId = array_map('intval', $listeId);
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_repos HR
                    INNER JOIN conges_users CU ON (HR.login = CU.u_login)
                WHERE id_heure IN (' . implode(',', $listeId) . ')
                ORDER BY debut DESC, statut ASC';

        return $sql->query($req)->fetch_all(\MYSQLI_ASSOC);
    }
}
