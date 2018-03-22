<?php
namespace LibertAPI\Utilisateur;

use LibertAPI\Tools\Libraries\AEntite;

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
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function getStorage2Entite(array $dataDao)
    {
        return [
            'id' => $dataDao['id'],
            'login' => $dataDao['u_login'],
            'nom' => $dataDao['u_nom'],
            'prenom' => $dataDao['u_prenom'],
            'isResp' => $dataDao['u_is_resp'] === 'Y',
            'isAdmin' => $dataDao['u_is_admin'] === 'Y',
            'isHr' => $dataDao['u_is_hr'] === 'Y',
            'isActive' => $dataDao['u_is_active'] === 'Y',
            'password' => $dataDao['u_passwd'],
            'quotite' => $dataDao['u_quotite'],
            'email' => $dataDao['u_email'],
            'numeroExercice' => $dataDao['u_num_exercice'],
            'planningId' => $dataDao['planning_id'],
            'heureSolde' => $dataDao['u_heure_solde'],
            'dateInscription' => $dataDao['date_inscription'],
            'token' => $dataDao['token'],
            'dateLastAccess' => $dataDao['date_last_access'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres)
    {
        $this->queryBuilder->select('*, u_login AS id');
        $this->setWhere($parametres);
        $res = $this->queryBuilder->execute();
        $data = $res->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($data)) {
            throw new \UnexpectedValueException('No resource match with these parameters');
        }

        $entites = [];
        foreach ($data as $value) {
            $entite = new UtilisateurEntite($this->getStorage2Entite($value));
            $entites[$entite->getId()] = $entite;
        }

        return $entites;
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * @inheritDoc
     */
    public function post(AEntite $entite)
    {
    }

    /**
     * @inheritDoc
     */
    public function put(AEntite $entite)
    {
        $this->queryBuilder->update($this->getTableName());
        $this->setSet($this->getEntite2Storage($entite));
        $this->queryBuilder->where('u_login = :id');
        $this->queryBuilder->setParameter(':id', $entite->getId());

        $this->queryBuilder->execute();
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2Storage(AEntite $entite)
    {
        return [
            //'u_login' => $entite->getLogin(), // PK ne doit pas être vu par la DAO
            /*'u_nom' => $entite->getJourId(),
            'u_prenom' => $entite->getTypeSemaine(),
            'u_is_resp' => $entite->getTypePeriode(),
            'u_is_admin' => $entite->getDebut(),
            'u_is_hr' => $entite->getFin(),
            'u_is_active' => $entite->getFin(),
            'u_passwd' => $entite->getFin(),
            'u_quotite' => $entite->getFin(),
            'u_email' => $entite->getFin(),
            'u_num_exercice' => $entite->getFin(),
            'planning_id' => $entite->getFin(),
            'u_heure_solde' => $entite->getFin(),
            'date_inscription' => $entite->getFin(),*/
            'token' => $entite->getToken(),
            'date_last_access' => $entite->getDateLastAccess(),
        ];
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
        if (!empty($parametres['is_active'])) {
            $this->queryBuilder->andWhere('u_is_active = :actif');
            $this->queryBuilder->setParameter(':actif', ($parametres['is_active']) ? 'Y' : 'N');
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
