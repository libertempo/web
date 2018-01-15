<?php
namespace LibertAPI\Tests\Units\Utilisateur;

use LibertAPI\Utilisateur\UtilisateurRepository as _Repository;
use LibertAPI\Tools\Libraries\AEntite;

/**
 * Classe de test du repository de l'utilisateur
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class UtilisateurRepository extends \Atoum
{
    /**
     * @var \LibertAPI\Tools\Libraries\Application Mock de la bibliothèque d'application
     */
    private $application;

    /**
     * @var \LibertAPI\Utilisateur\Dao Mock du DAO de l'utilisateur
     */
    private $dao;

    /**
     * @var \Doctrine\DBAL\Connection Mock du connecteur
     */
    private $connector;

    /**
     * @var \Doctrine\DBAL\Statement Mock du curseur de résultat
     */
    private $statement;

    /**
     * @var \LibertAPI\Utilisateur\Entite Mock de l'Entité de l'utilisateur
     */
    private $entite;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\LibertAPI\Utilisateur\UtilisateurDao();
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->statement = new \mock\Doctrine\DBAL\Statement();
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->connector = new \mock\Doctrine\DBAL\Connection();
        $this->connector->getMockController()->query = $this->statement;
        $this->mockGenerator->orphanize('__construct');
        $this->application = new \mock\LibertAPI\Tools\Libraries\Application($this->connector);
        $this->mockGenerator->orphanize('__construct');
        $this->entite = new \mock\LibertAPI\Utilisateur\UtilisateurEntite();
        $this->entite->getMockController()->getNom = 'Aladdin';
        $this->entite->getMockController()->getDateInscription = '222';
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
            $application2 = new \mock\LibertAPI\Tools\Libraries\Application($this->connector);
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
                'date_last_access' => 3,
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
                'date_last_access' => 134,
            ],
        ];
        $repository = new _Repository($this->dao);

        $entite = $repository->find([]);

        $this->object($entite)->isInstanceOf('\LibertAPI\Tools\Libraries\AEntite');
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
            'date_last_access' => 98,
        ]];
        $repository = new _Repository($this->dao);

        $entites = $repository->getList([]);

        $this->array($entites)->hasKey('Aladdin');
        $this->object($entites['Aladdin'])->isInstanceOf('\LibertAPI\Tools\Libraries\AEntite');
    }

    /*************************************************
     * POST
     *************************************************/


    public function testPostOne()
    {
        $this->variable((new _Repository($this->dao))->postOne([], $this->entite))->isNull();
    }

    /*************************************************
     * PUT
     *************************************************/

    public function testPutOne()
    {
        $this->variable((new _Repository($this->dao))->putOne([], $this->entite))->isNull();
    }

    public function testUpdateDateLastAccess()
    {
        $repo = new _Repository($this->dao);
        $this->entite->getMockController()->getToken = 'Tartuffe';

        $repo->updateDateLastAccess($this->entite);

        $this->mock($this->entite)->call('updateDateLastAccess')->once();
        $this->mock($this->dao)->call('put')->once();

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
            $repository->regenerateToken($this->entite);
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
        $this->entite->getMockController()->populateToken = function () {
            throw new \DomainException('');
        };

        $this->exception(function () use ($repository) {
            $repository->regenerateToken($this->entite);
        })->isInstanceOf('\DomainException');
    }

    public function testRegenerateTokenOk()
    {
        $repository = new _Repository($this->dao);
        $this->application->getMockController()->getTokenInstance = 'vi veri veniversum vivus vici';
        $repository->setApplication($this->application);
        $this->entite->getMockController()->populateToken = '';
        $this->entite->getMockController()->getToken = 'Pedro l\'asticot';
        $this->dao->getMockController()->put = '';

        $entite = $repository->regenerateToken($this->entite);

        $this->object($entite)->isInstanceOf(AEntite::class);
    }

    /*************************************************
     * DELETE
     *************************************************/

    public function testDeleteOne()
    {
        $this->variable((new _Repository($this->dao))->deleteOne($this->entite))->isNull();
    }
}
