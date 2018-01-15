<?php
namespace LibertAPI\Tests\Units\Planning;

use \LibertAPI\Planning\PlanningRepository as _Repository;

/**
 * Classe de test du repository de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class PlanningRepository extends \Atoum
{
    /**
     * @var \LibertAPI\Planning\PlanningDao Mock du DAO du planning
     */
    private $dao;

    /**
     * @var \LibertAPI\Planning\PlanningEntite Mock de l'Entité de planning
     */
    private $entite;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\LibertAPI\Planning\PlanningDao();
        $this->mockGenerator->orphanize('__construct');
        $this->entite = new \mock\LibertAPI\Planning\PlanningEntite();
        $this->entite->getMockController()->getId = 42;
        $this->entite->getMockController()->getName = 12;
        $this->entite->getMockController()->getStatus = 12;
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Teste la méthode getOne avec un id non trouvé
     */
    public function testGetOneNotFound()
    {
        $this->dao->getMockController()->getById = [];
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->getOne(99);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode getOne avec un id trouvé
     */
    public function testGetOneFound()
    {
        $this->dao->getMockController()->getById = [
            'planning_id' => '42',
            'name' => 'H2G2',
            'status' => '8',
        ];
        $repository = new _Repository($this->dao);

        $entite = $repository->getOne(42);

        $this->object($entite)->isInstanceOf('\LibertAPI\Tools\Libraries\AEntite');
        $this->integer($entite->getId())->isIdenticalTo(42);
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
            'planning_id' => '42',
            'name' => 'H2G2',
            'status' => '8',
        ]];
        $repository = new _Repository($this->dao);

        $entites = $repository->getList([]);

        $this->array($entites)->hasKey(42);
        $this->object($entites[42])->isInstanceOf('\LibertAPI\Tools\Libraries\AEntite');
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode postOne avec un champ manquant
     */
    public function testPostOneMissingArg()
    {
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->postOne(['name' => 'bob'], new \mock\LibertAPI\Planning\PlanningEntite([]));
        })->isInstanceOf('\LibertAPI\Tools\Exceptions\MissingArgumentException');
    }

    /**
     * Teste la méthode postOne avec un champ incohérent
     */
    public function testPostOneBadDomain()
    {
        $repository = new _Repository($this->dao);
        $entite = new \mock\LibertAPI\Planning\PlanningEntite([]);
        $entite->getMockController()->populate = function () {
            throw new \DomainException('');
        };

        $this->exception(function () use ($repository, $entite) {
            $repository->postOne(['name' => 'bob', 'status' => 'bab'], $entite);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode postOne quand tout est ok
     */
    public function testPostOneOk()
    {
        $repository = new _Repository($this->dao);
        $entite = new \mock\LibertAPI\Planning\PlanningEntite([]);
        $entite->getMockController()->populate = '';
        $entite->getMockController()->getName = 'name';
        $entite->getMockController()->getStatus = 'status';
        $this->dao->getMockController()->post = 3;

        $post = $repository->postOne(['name' => 'bob', 'status' => 'pop'], $entite);

        $this->integer($post);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Teste la méthode putOne avec un champ manquant
     */
    public function testPutOneMissingArgument()
    {
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->putOne(['name' => 'bob'], new \mock\LibertAPI\Planning\PlanningEntite([]));
        })->isInstanceOf('\LibertAPI\Tools\Exceptions\MissingArgumentException');
    }

    /**
     * Teste la méthode putOne avec un champ incohérent
     */
    public function testPutOneBadDomain()
    {
        $repository = new _Repository($this->dao);
        $entite = new \mock\LibertAPI\Planning\PlanningEntite([]);
        $entite->getMockController()->populate = function () {
            throw new \DomainException('');
        };

        $this->exception(function () use ($repository, $entite) {
            $repository->putOne(['name' => 'bob', 'status' => 'bab'], $entite);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode putOne tout ok
     */
    public function testPutOneOk()
    {
        $repository = new _Repository($this->dao);

        $result = $repository->putOne(['name' => 'baba', 'status' => 4], $this->entite);

        $this->variable($result)->isNull();
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Teste le fallback de la méthode deleteOne
     */
    public function testDeleteFallback()
    {
        $this->dao->getMockController()->delete = function () {
            throw new \LogicException('');
        };
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->deleteOne(new \mock\LibertAPI\Planning\PlanningEntite([]));
        })->isInstanceOf('\LogicException');

    }

    /**
     * Teste la méthode deleteOne tout ok
     */
    public function testDeleteOk()
    {
        $this->dao->getMockController()->delete = 4;
        $repository = new _Repository($this->dao);
        $entite = new \mock\LibertAPI\Planning\PlanningEntite([]);

        $this->variable($repository->deleteOne($entite))->isNull();
    }
}
