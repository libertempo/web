<?php declare(strict_types = 1);
namespace LibertAPI\Groupe\Responsable;

use LibertAPI\Tools\Libraries\AEntite;
use LibertAPI\Utilisateur\UtilisateurEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 *
 * Ne devrait être contacté que par ResponsableRepository
 * Ne devrait contacter personne
 */
class ResponsableDao extends \LibertAPI\Tools\Libraries\ADao
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * @inheritDoc
     */
    public function getById(int $id) : AEntite
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres) : array
    {
        $this->queryBuilder->select('users.*, users.u_login AS id');
        $this->queryBuilder->innerJoin('current', 'conges_users', 'users', 'current.gr_login = u_login');
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

    /**
     * @inheritDoc
     *
     * Duplication de la fonction dans UtilisateurDao (Cf. decisions.md #2018-02-17)
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
            'seeAll' => $dataDao['u_see_all'] === 'Y',
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

    /*************************************************
     * POST
     *************************************************/

    /**
     * @inheritDoc
     */
    public function post(AEntite $entite) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * @inheritDoc
     */
    public function put(AEntite $entite)
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

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * @inheritDoc
     */
    public function delete(int $id) : int
    {
        throw new \RuntimeException('Action is forbidden');
    }

    /**
     * Définit les filtres à appliquer à la requête
     *
     * @param array $parametres
     * @example [filter => []]
     */
    private function setWhere(array $parametres)
    {
        if (!empty($parametres['id'])) {
            $this->queryBuilder->andWhere('g_gid = :id');
            $this->queryBuilder->setParameter(':id', (int) $parametres['id']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_groupe_resp';
    }
}
