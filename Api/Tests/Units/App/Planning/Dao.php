<?php
namespace Api\Tests\Units\App\Planning;

use \Api\App\Planning\Dao as _Dao;

/**
 *
 */
final class Dao extends \Atoum
{
    /**
     * @var \mock\PDO Mock du connecteur
     */
    private $connector;

    /**
     * @var \mock\PDOStatement Mock du curseur de résultat PDO
     */
    private $statement;

    /**
     *
     */
    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->statement = new \mock\PDOStatement();
        $this->statement->getMockController()->execute = '';
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->connector = new \mock\PDO();
        $this->connector->getMockController()->prepare = $this->statement;
    }

    // getList trouvé
    // getList non trouvé

    /**
     * Teste la méthode getById avec un id non trouvé
     */
    public function testGetByIdNotFound()
    {
        $this->statement->getMockController()->fetch = [];
        $dao = new _Dao($this->connector);

        $get = $dao->getById(99);

        $this->array($get)->isEmpty();
    }

    /**
     * Teste la méthode getById avec un id trouvé
     */
    public function testGetByIdFound()
    {
        $this->statement->getMockController()->fetch = ['a'];
        $dao = new _Dao($this->connector);

        $get = $dao->getById(99);

        $this->array($get)->isNotEmpty();
    }

    /**
     * Teste la méthode getList avec des critères non pertinents
     */
    public function testGetListNotFound()
    {
        $this->statement->getMockController()->fetchAll = [];
        $dao = new _Dao($this->connector);

        $get = $dao->getList([]);

        $this->array($get)->isEmpty();
    }

    /**
    * Teste la méthode getList avec des critères pertinents
     */
    public function testGetListFound()
    {
        $this->statement->getMockController()->fetchAll = [['a']];
        $dao = new _Dao($this->connector);

        $get = $dao->getList([]);

        $this->array($get[0])->isNotEmpty();
    }
}
