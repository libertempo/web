<?php
namespace App\Components\Utilisateur;

use App\Libraries\AModel;
use App\Libraries\Application;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 * @see \Tests\Units\App\Components\Utilisateur\Repository
 *
 * Ne devrait être contacté que par le Authentification\Controller
 * Ne devrait contacter que le Utilisateur\Model, Utilisateur\Dao
 */
class Repository extends \App\Libraries\ARepository
{
    /**
     * @var Application Bibliothèque d'accès aux données de l'application
     */
    private $application;

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
     * @return AModel
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

        $models = [];
        foreach ($data as $value) {
            $model = new Model($this->getDataDao2Model($value));
            $models[$model->getId()] = $model;
        }

        return $models;
    }

    /**
     * @inheritDoc
     */
    final protected function getDataDao2Model(array $dataDao)
    {
        return [
            'id' => $dataDao['id'],
            'login' => $dataDao['u_login'],
            'nom' => $dataDao['u_nom'],
            'prenom' => $dataDao['u_prenom'],
            'isResp' => $dataDao['u_is_resp'],
            'isAdmin' => $dataDao['u_is_admin'],
            'isHr' => $dataDao['u_is_hr'],
            'isActive' => $dataDao['u_is_active'],
            'seeAll' => $dataDao['u_see_all'],
            'password' => $dataDao['u_passwd'],
            'quotite' => $dataDao['u_quotite'],
            'email' => $dataDao['u_email'],
            'numeroExercice' => $dataDao['u_num_exercice'],
            'planningId' => $dataDao['planning_id'],
            'heureSolde' => $dataDao['u_heure_solde'],
            'dateInscription' => $dataDao['date_inscription'],
            'token' => $dataDao['token'],
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
        return $results;
    }

    /*************************************************
     * POST
     *************************************************/

    public function postOne(array $data, AModel $model)
    {
    }

    /*************************************************
     * PUT
     *************************************************/

    public function putOne(array $data, AModel $model)
    {
    }

    /**
     * Regénère le token de l'utilisateur pour une nouvelle session
     *
     * @param AModel $model Modèle utilisateur
     *
     * @return AModel Le modèle hydraté du nouveau token
     */
    public function regenerateToken(AModel $model)
    {
        $instanceToken = $this->application->getTokenInstance();
        if ('' === $instanceToken) {
            throw new \RuntimeException('Instance token is not set');
        }

        try {
            $model->populateToken($this->buildToken($instanceToken, $model));
            $dataDao = $this->getModel2DataDao($model);
            $this->dao->put($dataDao, $model->getId());

            return $model;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getModel2DataDao(AModel $model)
    {
        return [
            //'u_login' => $model->getLogin(), // PK ne doit pas être vu par la DAO
            /*'u_nom' => $model->getJourId(),
            'u_prenom' => $model->getTypeSemaine(),
            'u_is_resp' => $model->getTypePeriode(),
            'u_is_admin' => $model->getDebut(),
            'u_is_hr' => $model->getFin(),
            'u_is_active' => $model->getFin(),
            'u_see_all' => $model->getFin(),
            'u_passwd' => $model->getFin(),
            'u_quotite' => $model->getFin(),
            'u_email' => $model->getFin(),
            'u_num_exercice' => $model->getFin(),
            'planning_id' => $model->getFin(),
            'u_heure_solde' => $model->getFin(),
            'date_inscription' => $model->getFin(),*/
            'token' => $model->getToken(),
        ];
    }

    /**
     * Génère le token
     * @example factory method (/ strategy) pour la génération et aider à la reconnaissance du pattern de l'autre côté ?
     *
     * @return string
     */
    private function buildToken($instanceToken, AModel $model)
    {
        // assertion sur la vacuite de nom, dateInscriptionUtilisateur, dateJour et id
        $dateJour = date('Y-m-d');
        $preHash = $dateJour . ']#[' . $model->getNom() . ']#[' . $model->getDateInscription() . ']#[' . $model->getId() . ']#[' . $dateJour;

        return $instanceToken ^ $preHash;
    }

    /*************************************************
     * DELETE
     *************************************************/

    public function deleteOne(AModel $model)
    {
    }
}
