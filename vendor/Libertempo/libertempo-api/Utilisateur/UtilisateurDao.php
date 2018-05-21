<?php declare(strict_types = 1);
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
    public function getById(int $id) : AEntite
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function getStorage2Entite(array $dataDao) : array
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
    public function getList(array $parametres) : array
    {
        $this->queryBuilder->select('*, u_login AS id');
        $this->setWhere($parametres);
        $res = $this->queryBuilder->execute();
        $data = $res->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($data)) {
            throw new \UnexpectedValueException('No resource match with these parameters');
        }

        $entites = array_map(function ($value) {
            return new UtilisateurEntite($this->getStorage2Entite($value));
        }, $data);

        return $entites;
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * @inheritDoc
     */
    public function post(AEntite $entite) : int
    {
        throw new \RuntimeException('Not implemented');
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
    final protected function getEntite2Storage(AEntite $entite) : array
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

    public function delete(int $id) : int
    {
        throw new \Exception('Not implemented');
    }

    /**
     * Définit les values à insérer
     *
     * @param array $parametres
     */
    private function setWhere(array $parametres)
    {
        $whereCriteria = [];
        if (!empty($parametres['u_login'])) {
            $this->queryBuilder->andWhere('u_login = :id');
            $whereCriteria[':id'] = $parametres['u_login'];
        }
        if (!empty($parametres['u_passwd'])) {
            // @TODO: on vise la compat' dans la migration de #12,
            // mais il faudra à terme enlever md5
            $this->queryBuilder->andWhere('u_passwd = :passwordMd5 OR u_passwd = :passwordBlow');
            $whereCriteria[':passwordMd5'] = md5($parametres['u_passwd']);
            $whereCriteria[':passwordBlow'] = password_hash($parametres['u_passwd'], PASSWORD_BCRYPT);
        }
        if (!empty($parametres['token'])) {
            $this->queryBuilder->andWhere('token = :token');
            $whereCriteria[':token'] = $parametres['token'];
        }
        if (!empty($parametres['gt_date_last_access'])) {
            $this->queryBuilder->andWhere('date_last_access >= :gt_date_last_access');
            $whereCriteria[':gt_date_last_access'] = $parametres['gt_date_last_access'];
        }
        if (!empty($parametres['is_active'])) {
            $this->queryBuilder->andWhere('u_is_active = :actif');
            $whereCriteria[':actif'] = ($parametres['is_active']) ? 'Y' : 'N';
        }
        if (!empty($whereCriteria)) {
            $this->queryBuilder->setParameters($whereCriteria);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_users';
    }
}
