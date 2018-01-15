<?php
namespace LibertAPI\Tests\Units\Tools\Libraries;

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

}
