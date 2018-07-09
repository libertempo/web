<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Tools\Libraries;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * Classe de test des repositories
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.6
 */
abstract class ARepository extends \Atoum
{
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->result = new \mock\Doctrine\DBAL\Statement();
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->queryBuilder = new \mock\Doctrine\DBAL\Query\QueryBuilder();
        $this->calling($this->queryBuilder)->execute = $this->result;
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->connector = new \mock\Doctrine\DBAL\Connection();
        $this->calling($this->connector)->createQueryBuilder = $this->queryBuilder;

    }

    public function testGetOneEmpty()
    {
        $this->newTestedInstance($this->connector);
        $this->calling($this->result)->fetch = [];

        $this->exception(function () {
            $this->testedInstance->getOne(4);
        })->isInstanceOf(\DomainException::class);
    }

    public function testGetListEmpty()
    {
        $this->newTestedInstance($this->connector);
        $this->calling($this->result)->fetchAll = [];

        $this->exception(function () {
            $this->testedInstance->getList([]);
        })->isInstanceOf(\UnexpectedValueException::class);
    }

    public function testGetListOk()
    {
        $this->newTestedInstance($this->connector);
        $this->calling($this->result)->fetchAll = [$this->getStorageContent()];

        $res = $this->testedInstance->getList([]);

        $this->array($res)->hasSize(1);
        array_walk($res, function ($element) {
            $this->object($element)->isInstanceOf(AEntite::class);
        });
    }

    abstract protected function getStorageContent() : array;

    public function testPostOne()
    {
        $this->newTestedInstance($this->connector);
        $this->calling($this->queryBuilder)->execute = true;
        $this->calling($this->connector)->lastInsertId = 9182;

        $this->integer($this->testedInstance->postOne([], new \mock\LibertAPI\Tools\Libraries\AEntite([])))->isIdenticalTo(9182);
    }

    public function testPutOne()
    {
        $this->newTestedInstance($this->connector);
        $this->calling($this->queryBuilder)->execute = true;
        $this->calling($this->connector)->lastInsertId = 9182;

        $this->variable($this->testedInstance->putOne(new \mock\LibertAPI\Tools\Libraries\AEntite([])))->isNull();
    }

    public function testDeleteOne()
    {
        $this->newTestedInstance($this->connector);
        $this->calling($this->queryBuilder)->execute = $this->result;
        $this->calling($this->result)->rowCount = 123;

        $this->integer($this->testedInstance->deleteOne(new \mock\LibertAPI\Tools\Libraries\AEntite([])))->isIdenticalTo(123);
    }

    /**
     * @var \Doctrine\DBAL\Connection Mock du connecteur
     */
    protected $connector;

    /**
     * @var \Doctrine\DBAL\Query\QueryBuilder Mock du queryBuilder
     */
    protected $queryBuilder;

    /**
     * @var \Doctrine\DBAL\Statement Curseur de résultats
     */
    protected $result;
}
