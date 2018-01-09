<?php
namespace App\Components\Utilisateur;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 *
 * Ne devrait être contacté que par Utilisateur\Repository
 * Ne devrait contacter personne
 */
class Dao extends \App\Libraries\ADao
{
    public function getById($id)
    {
    }

    /**
     * @inheritDoc
     * @todo
     */
    public function getList(array $parametres)
    {
        $req = 'SELECT *, u_login AS id FROM ' . $this->getTableName();
        $filters = $this->getFilters($parametres);
        $req .= $filters['where'];
        $res = $this->storageConnector->prepare($req);
        $res->execute($filters['bind']);

        return $res->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne le tableau des filtres à appliquer à la requête
     *
     * @param array $parametres
     * @example [filter => [], lt => 23, limit => 4]
     *
     * @return array ['where' => clause complète, 'bind' => variables[]]
     */
    private function getFilters(array $parametres)
    {
        $where = [];
        $bind = [];
        if (!empty($parametres['u_login'])) {
            $where[] = 'u_login = :u_login';
            $bind[':u_login'] = $parametres['u_login'];
        }
        if (!empty($parametres['u_passwd'])) {
            $where[] = 'u_passwd = :u_passwd';
            $bind[':u_passwd'] = $parametres['u_passwd'];
        }
        if (!empty($parametres['token'])) {
            $where[] = 'token = :token';
            $bind[':token'] = $parametres['token'];
        }
        if (!empty($parametres['gt_date_last_access'])) {
            $where[] = 'date_last_access >= :gt_date_last_access';
            $bind[':gt_date_last_access'] = $parametres['gt_date_last_access'];
        }

        return [
            'where' => !empty($where)
                ? ' WHERE ' . implode(' AND ', $where)
                : '',
            'bind' => $bind,
        ];
    }

    /*************************************************
     * POST
     *************************************************/

    public function post(array $a)
    {
    }

    /**
     * Met à jour une ressource
     *
     * @param array $data Données à mettre à jour
     * @param string $id Identifiant de l'élément (passer en int)
     */
    public function put(array $data, $id)
    {
        $req = 'UPDATE ' . $this->getTableName() . '
            SET token = :token, date_last_access = :date_last_access
            WHERE u_login = :id';
        $res = $this->storageConnector->prepare($req);
        $res->execute([
            'token' => $data['token'],
            'date_last_access' => $data['date_last_access'],
            'id' => $id,
        ]);
    }

    /*************************************************
     * DELETE
     *************************************************/

    public function delete($id)
    {
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName()
    {
        return 'conges_users';
    }
}
