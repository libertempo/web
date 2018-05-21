<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Tools\Libraries;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * Classe commune de test du DAO
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
abstract class ADao extends \Atoum
{
    /**
     * Init des tests
     *
     * @param string $method Méthode en cours de test
     */
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

    /**
     * Teste la méthode getById avec un id non trouvé
     */
    public function testGetByIdNotFound()
    {
        $this->calling($this->result)->fetch = [];
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->getById(99);
        })->isInstanceOf(\DomainException::class);
    }

    /**
     * Teste la méthode getById avec un id trouvé
     */
    public function testGetByIdFound()
    {
        $this->calling($this->result)->fetch = $this->getStorageContent();
        $this->newTestedInstance($this->connector);

        $get = $this->testedInstance->getById(99);

        $this->object($get)->isInstanceOf(AEntite::class);
    }

    /**
     * Teste la méthode getList avec des critères non pertinents
     */
    public function testGetListNotFound()
    {
        $this->calling($this->result)->fetchAll = [];
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->getList([]);
        })->isInstanceOf(\UnexpectedValueException::class);
    }

    /**
     * Teste la méthode getList avec des critères pertinents
     */
    public function testGetListFound()
    {
        $this->calling($this->result)->fetchAll = [$this->getStorageContent()];
        $this->newTestedInstance($this->connector);

        $get = $this->testedInstance->getList([]);

        $this->array($get)->isNotEmpty();
        array_map(function ($entite) {
            $this->object($entite)->isInstanceOf(AEntite::class);
        }, $get);
    }

    abstract protected function getStorageContent();

    /**
     * Teste la méthode delete quand tout est ok
     */
    public function testDeleteOk()
    {
        $this->calling($this->result)->rowCount = 1;
        $this->newTestedInstance($this->connector);

        $res = $this->testedInstance->delete(7);

        $this->integer($res)->isIdenticalTo(1);
    }
}
