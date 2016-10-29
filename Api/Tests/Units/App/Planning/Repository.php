<?php
namespace Api\Tests\Units\App\Planning;

use \Api\App\Planning\Repository as _Repository;

/**
 * Classe de test du contrôleur de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Repository extends \Atoum
{
    /**
     * @var \mock\Api\App\Planning\Dao $dao Mock du DAO du planning
     */
    private $dao;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\Api\App\Planning\Dao();
    }

    // getListeFound
    // getListeNotFound

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

        $model = $repository->getOne(99);

        $this->object($model)->isInstanceOf('\Api\App\Libraries\Model');
        $this->integer($model->getId())->isIdenticalTo(99);
    }
}
