<?php
namespace Tests\Units\App\Components\Utilisateur;

use \App\Components\Utilisateur\Repository as _Repository;
use App\Libraries\AModel;

/**
 * Classe de test du repository de l'utilisateur
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class Repository extends \Atoum
{
    /**
     * @var \mock\App\Libraries\Application Mock de la bibliothèque d'application
     */
    private $application;

    /**
     * @var \mock\App\Components\Utilisateur\Dao Mock du DAO de l'utilisateur
     */
    private $dao;

    /**
     * @var \mock\PDO Mock du connecteur
     */
    private $connector;

    /**
     * @var \mock\PDOStatement Mock du curseur de résultat PDO
     */
    private $statement;

    /**
     * @var \mock\App\Components\Utilisateur\Model Mock du Modèle de l'utilisateur
     */
    private $model;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\App\Components\Utilisateur\Dao();
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->statement = new \mock\PDOStatement();
        $this->statement->getMockController()->fetchAll = [];
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->connector = new \mock\PDO();
        $this->connector->getMockController()->query = $this->statement;
        $this->application = new \mock\App\Libraries\Application($this->connector);
        $this->mockGenerator->orphanize('__construct');
        $this->model = new \mock\App\Components\Utilisateur\Model();
        $this->model->getMockController()->getNom = 'Aladdin';
        $this->model->getMockController()->getDateInscription = '222';
    }

    /**
     * Teste la méthode setApplication exécutée une fois
     */
    public function testSetApplicationOnce()
    {
        $repository = new _Repository($this->dao);

        $this->variable($repository->setApplication($this->application))->isNull();
    }

    /**
     * Teste la méthode setApplication exécutée deux fois
     */
    public function testSetApplicationTwice()
    {
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->setApplication($this->application);
            $application2 = new \mock\App\Libraries\Application($this->connector);
            $repository->setApplication($application2);
            $repository->getList([]);
        })->isInstanceOf('\LogicException');
    }

    /*************************************************
     * GET
     *************************************************/

    public function testGetOne()
    {
        $this->variable((new _Repository($this->dao))->getOne(1))->isNull();
    }

    /**
     * Teste la méthode find avec des critères pertinents
     */
    public function testFind()
    {
        $this->dao->getMockController()->getList = [
            [
                'id' => 'Aladdin',
                'u_login' => 'Aladdin',
                'u_passwd' => 'OpenSesame',
                'u_nom' => 'Aladdin',
                'u_prenom' => 'Aladdin',
                'u_is_resp' => 'Y',
                'u_is_admin' => 'N',
                'u_is_hr' => 'N',
                'u_is_active' => 'N',
                'u_see_all' => 'N',
                'u_quotite' => 1000,
                'u_email' => 'aladdin@tapisvolant.net',
                'u_num_exercice' => 98,
                'planning_id' => 12,
                'u_heure_solde' => 983,
                'date_inscription' => 2,
                'token' => '',
            ],
            [
                'id' => 'Sinbad',
                'u_login' => 'Sinbad',
                'u_nom' => 'Sinbad',
                'u_prenom' => 'Sinbad',
                'u_is_resp' => 'N',
                'u_is_admin' => 'N',
                'u_is_hr' => 'N',
                'u_is_active' => 'N',
                'u_see_all' => 'N',
                'u_passwd' => 'Bassorah',
                'u_quotite' => 1000,
                'u_email' => 'sinbad@sea.com',
                'u_num_exercice' => 5,
                'planning_id' => 12,
                'u_heure_solde' => 1218,
                'date_inscription' => 2,
                'token' => '',
            ],
        ];
        $repository = new _Repository($this->dao);

        $model = $repository->find([]);

        $this->object($model)->isInstanceOf('\App\Libraries\AModel');
    }

    /**
     * Teste la méthode getList avec des critères non pertinents
     */
    public function testGetListNotFound()
    {
        $this->dao->getMockController()->getList = [];
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->getList([]);
        })->isInstanceOf('\UnexpectedValueException');
    }

    /**
     * Teste la méthode getList avec des critères pertinents
     */
    public function testGetListFound()
    {
        $this->dao->getMockController()->getList = [[
            'id' => 'Aladdin',
            'u_login' => 'Aladdin',
            'u_passwd' => 'OpenSesame',
            'u_nom' => 'Aladdin',
            'u_prenom' => 'Aladdin',
            'u_is_resp' => 'Y',
            'u_is_admin' => 'N',
            'u_is_hr' => 'N',
            'u_is_active' => 'N',
            'u_see_all' => 'N',
            'u_quotite' => 1000,
            'u_email' => 'aladdin@tapisvolant.net',
            'u_num_exercice' => 98,
            'planning_id' => 12,
            'u_heure_solde' => 983,
            'date_inscription' => 2,
            'token' => '',
        ]];
        $repository = new _Repository($this->dao);

        $models = $repository->getList([]);

        $this->array($models)->hasKey('Aladdin');
        $this->object($models['Aladdin'])->isInstanceOf('\App\Libraries\AModel');
    }

    /*************************************************
     * POST
     *************************************************/


    public function testPostOne()
    {
        $this->variable((new _Repository($this->dao))->postOne([], $this->model))->isNull();
    }

    /*************************************************
     * PUT
     *************************************************/

    public function testPutOne()
    {
        $this->variable((new _Repository($this->dao))->putOne([], $this->model))->isNull();
    }

    /**
     * Teste la méthode generateToken avec un token d'instance vide
     */
    public function testRegenerateTokenWithTokenInstanceEmpty()
    {
        $repository = new _Repository($this->dao);
        $this->application->getMockController()->getTokenInstance = '';
        $repository->setApplication($this->application);

        $this->exception(function () use ($repository) {
            $repository->regenerateToken($this->model);
        })->isInstanceOf('\RuntimeException');
    }

    /**
     * Teste la méthode generateToken avec un token incohérent
     */
    public function testRegenerateTokenBadDomain()
    {
        $repository = new _Repository($this->dao);
        $this->application->getMockController()->getTokenInstance = 'vi veri veniversum vivus vici';
        $repository->setApplication($this->application);
        $this->model->getMockController()->populateToken = function () {
            throw new \DomainException('');
        };

        $this->exception(function () use ($repository) {
            $repository->regenerateToken($this->model);
        })->isInstanceOf('\DomainException');
    }

    public function testRegenerateTokenOk()
    {
        $repository = new _Repository($this->dao);
        $this->application->getMockController()->getTokenInstance = 'vi veri veniversum vivus vici';
        $repository->setApplication($this->application);
        $this->model->getMockController()->populateToken = '';
        $this->model->getMockController()->getToken = 'Pedro l\'asticot';
        $this->dao->getMockController()->put = '';

        $model = $repository->regenerateToken($this->model);

        $this->object($model)->isInstanceOf(AModel::class);
    }

    /*************************************************
     * DELETE
     *************************************************/

    public function testDeleteOne()
    {
        $this->variable((new _Repository($this->dao))->deleteOne($this->model))->isNull();
    }
}
