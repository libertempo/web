<?php
namespace LibertAPI\Utilisateur;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
class UtilisateurDao extends \LibertAPI\Tools\Libraries\ADao
{
    public function getById($id)
    {
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres)
    {
        $this->queryBuilder->select('*, u_login AS id');
        $this->setWhere($parametres);
        $res = $this->queryBuilder->execute();

        return $res->fetchAll(\PDO::FETCH_ASSOC);
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
        $this->queryBuilder->update($this->getTableName());
        $this->setSet($data);
        $this->queryBuilder->where('u_login = :id');
        $this->queryBuilder->setParameter(':id', $id);

        $this->queryBuilder->execute();
    }

    private function setSet(array $parametres)
    {
        if (!empty($parametres['token'])) {
            $this->queryBuilder->set('token', ':token');
            $this->queryBuilder->setParameter(':token', $parametres['token']);
        }
        if (!empty($parametres['date_last_access'])) {
            $this->queryBuilder->set('date_last_access', ':date_last_access');
            $this->queryBuilder->setParameter(':date_last_access', $parametres['date_last_access']);
        }
    }

    /*************************************************
     * DELETE
     *************************************************/

    public function delete($id)
    {
    }

    /**
     * Définit les values à insérer
     *
     * @param array $parametres
     */
    private function setWhere(array $parametres)
    {
        if (!empty($parametres['u_login'])) {
            $this->queryBuilder->andWhere('u_login = :id');
            $this->queryBuilder->setParameter(':id', $parametres['u_login']);
        }
        if (!empty($parametres['u_passwd'])) {
            $this->queryBuilder->andWhere('u_passwd = :password');
            $this->queryBuilder->setParameter(':password', $parametres['u_passwd']);
        }
        if (!empty($parametres['token'])) {
            $this->queryBuilder->andWhere('token = :token');
            $this->queryBuilder->setParameter(':token', $parametres['token']);
        }
        if (!empty($parametres['gt_date_last_access'])) {
            $this->queryBuilder->andWhere('date_last_access >= :gt_date_last_access');
            $this->queryBuilder->setParameter(':gt_date_last_access', $parametres['gt_date_last_access']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName()
    {
        return 'conges_users';
    }
}
