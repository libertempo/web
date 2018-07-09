<?php declare(strict_types = 1);
namespace LibertAPI\Groupe\Employe;

use LibertAPI\Tools\Libraries\AEntite;
use \LibertAPI\Utilisateur\UtilisateurEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 */
class EmployeRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /**
     * @inheritDoc
     */
    public function getOne(int $id) : AEntite
    {
        throw new \RuntimeException('#' . $id . ' is not a callable resource');
    }

    final protected function getEntiteClass() : string
    {
        return UtilisateurEntite::class;
    }

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Storage(array $paramsConsumer) : array
    {
        unset($paramsConsumer);
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres) : array
    {
        $this->queryBuilder->select('users.*, users.u_login AS id');
        $this->queryBuilder->innerJoin('current', 'conges_users', 'users', 'current.gu_login = u_login');
        $this->setWhere($this->getParamsConsumer2Storage($parametres));
        $res = $this->queryBuilder->execute();

        $data = $res->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            throw new \UnexpectedValueException('No resource match with these parameters');
        }

        $entites = array_map(function ($value) {
            $entiteClass = $this->getEntiteClass();
            return new $entiteClass($this->getStorage2Entite($value));
        }, $data);

        return $entites;
    }

    /**
     * @inheritDoc
     *
     * Duplication de la fonction dans UtilisateurRepository (Cf. decisions.md #2018-02-17)
     */
    final protected function getStorage2Entite(array $dataStorage) : array
    {
        return [
            'id' => $dataStorage['id'],
            'login' => $dataStorage['u_login'],
            'nom' => $dataStorage['u_nom'],
            'prenom' => $dataStorage['u_prenom'],
            'isResp' => $dataStorage['u_is_resp'] === 'Y',
            'isAdmin' => $dataStorage['u_is_admin'] === 'Y',
            'isHr' => $dataStorage['u_is_hr'] === 'Y',
            'isActif' => $dataStorage['u_is_active'] === 'Y',
            'seeAll' => $dataStorage['u_see_all'] === 'Y',
            'password' => $dataStorage['u_passwd'],
            'quotite' => $dataStorage['u_quotite'],
            'email' => $dataStorage['u_email'],
            'numeroExercice' => $dataStorage['u_num_exercice'],
            'planningId' => $dataStorage['planning_id'],
            'heureSolde' => $dataStorage['u_heure_solde'],
            'dateInscription' => $dataStorage['date_inscription'],
            'token' => $dataStorage['token'],
            'dateLastAccess' => $dataStorage['date_last_access'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function postOne(array $data, AEntite $entite) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    public function putOne(AEntite $entite)
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2Storage(AEntite $entite) : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    final protected function setValues(array $values)
    {
        unset($values);
    }

    /**
     * @inheritDoc
     */
    final protected function setSet(array $parametres)
    {
        unset($parametres);
    }

    /**
     * @inheritDoc
     */
    public function deleteOne(AEntite $entite) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    final protected function setWhere(array $parametres)
    {
        if (!empty($parametres['id'])) {
            $this->queryBuilder->andWhere('gu_gid = :id');
            $this->queryBuilder->setParameter(':id', (int) $parametres['id']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_groupe_users';
    }
}
