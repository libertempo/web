<?php
namespace Api\Tests\Units\App\Components\Planning\Creneau;

use \Api\App\Components\Planning\Creneau\Repository as _Repository;

/**
 * Classe de test du repository de créneau de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Repository extends \Atoum
{
    /**
     * @var \mock\Api\App\Components\Planning\Creneau\Dao Mock du DAO du planning
     */
    private $dao;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\Api\App\Components\Planning\Creneau\Dao();
    }

    /**
     * Teste la méthode getOne avec un id non trouvé
     */
    public function testGetOneNotFound()
    {
        $this->dao->getMockController()->getById = [];
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->getOne(99, 23);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode getOne avec un id trouvé
     */
    public function testGetOneFound()
    {
        $this->dao->getMockController()->getById = [
            'creneau_id' => '42',
            'planning_id' => 99,
            'jour_id' => 99,
            'type_semaine' => 99,
            'type_periode' => 99,
            'debut' => 99,
            'fin' => 99,
        ];
        $repository = new _Repository($this->dao);

        $model = $repository->getOne(42, 23);

        $this->object($model)->isInstanceOf('\Api\App\Libraries\AModel');
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
            $repository->getList(['planningId' => 58]);
        })->isInstanceOf('\UnexpectedValueException');
    }

    /**
     * Teste la méthode getList avec des critères pertinents
     */
    public function testGetListFound()
    {
        $this->dao->getMockController()->getList = [[
            'creneau_id' => '42',
            'planning_id' => 99,
            'jour_id' => 99,
            'type_semaine' => 99,
            'type_periode' => 99,
            'debut' => 99,
            'fin' => 99,
        ]];
        $repository = new _Repository($this->dao);

        $models = $repository->getList(['planningId' => 53]);

        $this->array($models)->hasKey(42);
        $this->object($models[42])->isInstanceOf('\Api\App\Libraries\AModel');
    }
}
