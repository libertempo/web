<?php
namespace Api\Tests\Units\App\Components\Planning;

use \Api\App\Components\Planning\Repository as _Repository;

/**
 * Classe de test du repository de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Repository extends \Atoum
{
    /**
     * @var \mock\Api\App\Components\Planning\Dao Mock du DAO du planning
     */
    private $dao;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\Api\App\Components\Planning\Dao();
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

        $model = $repository->getOne(42);

        $this->object($model)->isInstanceOf('\Api\App\Libraries\Model');
        $this->integer($model->getId())->isIdenticalTo(42);
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

        $models = $repository->getList([]);

        $this->array($models)->hasKey(42);
        $this->object($models[42])->isInstanceOf('\Api\App\Libraries\Model');
    }

    /*************************************************
     * POST
     *************************************************/

    // test ok avec id de retour
    // test avec exception DomainException si valeur pas dans le bon domaine
    // test fallback

    /**
     * Teste la méthode postOne avec un champ manquant
     */
    public function testPostOneMissingArg()
    {
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->postOne(['name' => 'bob']);
        })->isInstanceOf('\Api\App\Exceptions\MissingArgumentException');
    }

    /**
     * Teste la méthode postOne avec un champ manquant
     */
    public function testPostOneBadDomain()
    {
        $repository = new _Repository($this->dao);
        $model = new \mock\Api\App\Components\Planning\Model([]);
        $model->getMockController()->populate = function () {
            throw new \DomainException('');
        };
        $repository->setModel($model);

        $this->exception(function () use ($repository) {
            $repository->postOne(['name' => 'bob', 'status' => 'bab']);
        })->isInstanceOf('\DomainException');
    }
}
