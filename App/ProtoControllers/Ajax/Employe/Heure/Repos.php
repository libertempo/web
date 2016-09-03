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
     *
     */
    public function getListe(array $parametresRecherche)
    {
        $repos = [];
        $liste = $this->getListeSQL($this->getListeId($parametresRecherche));

        foreach ($liste as $heureRepos) {
            $repos[] = [
                'start' => date('c', $heureRepos['debut']),
                'end' => date('c', $heureRepos['fin']),
                'className' => 'heureRepos',
                'title' => '« ' . $heureRepos['login'] . ' » - Repos',
            ];
        }

        return $repos;
    }

    /*
     * SQL
     */

    protected function getListeId(array $params)
    {
        $where = [];
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'start':
                        $where[] = 'debut >= ' . strtotime($value);
                        break;
                    case 'end':
                        $where[] = 'debut <= ' . strtotime($value);
                        break;
                }
            }
        }
        $where[] = 'duree > 0';
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT id_heure AS id
                FROM heure_repos '
                . ((!empty($where)) ? ' WHERE ' . implode(' AND ', $where) : '');
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     *
     */
    private function getListeSQL(array $listeId)
    {
        if (empty($listeId)) {
            return [];
        }
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM heure_repos
                WHERE id_heure IN (' . implode(',', $listeId) . ')
                ORDER BY debut DESC, statut ASC';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }
}
