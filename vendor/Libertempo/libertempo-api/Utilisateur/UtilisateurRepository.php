<?php
namespace LibertAPI\Utilisateur;

use LibertAPI\Tools\Libraries\AEntite;
use LibertAPI\Tools\Libraries\Application;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 * @see \LibertAPI\Tests\Units\Utilisateur\UtilisateurRepository
 */
class UtilisateurRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /**
     * @var Application Bibliothèque d'accès aux données de l'application
     */
    private $application;

    /**
     * Ajoute la bibliothèque d'accès aux données, pour la génération du token
     *
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        if ($this->application instanceof Application) {
            throw new \LogicException('Application can\'t be set twice');
        }
        $this->application = $application;
    }

    /*************************************************
     * GET
     *************************************************/

    public function getOne($id)
    {
    }

    /**
     * Retourne une ressource correspondant à des critères
     *
     * @param array $parametres
     * @example [offset => 4, start-after => 23, filter => 'name::chapo|status::1,3']
     *
     * @return AEntite
     */
    public function find(array $parametres)
    {
        $list = $this->getList($parametres);
        return reset($list);
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres)
    {
        $data = $this->dao->getList($this->getParamsConsumer2Dao($parametres));
        if (empty($data)) {
            throw new \UnexpectedValueException('No resource match with these parameters');
        }

        $entites = [];
        foreach ($data as $value) {
            $entite = new UtilisateurEntite($this->getDataDao2Entite($value));
            $entites[$entite->getId()] = $entite;
        }

        return $entites;
    }

    /**
     * @inheritDoc
     */
    final protected function getDataDao2Entite(array $dataDao)
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

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Dao(array $paramsConsumer)
    {
        $results = [];
        if (!empty($paramsConsumer['login'])) {
            $results['u_login'] = (string) $paramsConsumer['login'];
        }
        if (!empty($paramsConsumer['password'])) {
            $results['u_passwd'] = md5($paramsConsumer['password']);
        }
        if (!empty($paramsConsumer['token'])) {
            $results['token'] = (string) $paramsConsumer['token'];
        }
        if (!empty($paramsConsumer['gt_date_last_access'])) {
            $results['gt_date_last_access'] = (string) $paramsConsumer['gt_date_last_access'];
        }
        return $results;
    }

    /*************************************************
     * POST
     *************************************************/

    public function postOne(array $data, AEntite $entite)
    {
    }

    /*************************************************
     * PUT
     *************************************************/

    public function putOne(array $data, AEntite $entite)
    {
    }

    /**
     * « Ping » la date de dernier accès de l'utilisateur
     *
     * @param UtilisateurEntite $entite Entité utilisateur
     *
     * @since 0.3
     */
    public function updateDateLastAccess(UtilisateurEntite $entite)
    {
        $entite->updateDateLastAccess();
        $dataDao = $this->getEntite2DataDao($entite);
        $this->dao->put($dataDao, $entite->getId());
    }

    /**
     * Regénère le token de l'utilisateur pour une nouvelle session
     *
     * @param AEntite $entite Entité utilisateur
     *
     * @return AEntite L'entité hydratée du nouveau token
     * @throws \RuntimeException Si le token instance n'est pas posé
     */
    public function regenerateToken(AEntite $entite)
    {
        $instanceToken = $this->application->getTokenInstance();
        if (empty($instanceToken)) {
            throw new \RuntimeException('Instance token is not set');
        }

        try {
            $entite->populateToken($this->buildToken($instanceToken));
            $entite->updateDateLastAccess();
            $dataDao = $this->getEntite2DataDao($entite);
            $this->dao->put($dataDao, $entite->getId());

            return $entite;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2DataDao(AEntite $entite)
    {
        return [
            //'u_login' => $entite->getLogin(), // PK ne doit pas être vu par la DAO
            /*'u_nom' => $entite->getJourId(),
            'u_prenom' => $entite->getTypeSemaine(),
            'u_is_resp' => $entite->getTypePeriode(),
            'u_is_admin' => $entite->getDebut(),
            'u_is_hr' => $entite->getFin(),
            'u_is_active' => $entite->getFin(),
            'u_see_all' => $entite->getFin(),
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

    /**
     * Génère le token
     *
     * @param string $instanceToken Clé API de l'instance
     *
     * @return string
     */
    private function buildToken($instanceToken)
    {
        return password_hash($instanceToken, \PASSWORD_BCRYPT);
    }

    /*************************************************
     * DELETE
     *************************************************/

    public function deleteOne(AEntite $entite)
    {
    }
}
