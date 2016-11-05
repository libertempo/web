<?php
namespace Api\Tests\Units\App\Components\Planning\Creneau;

use \Api\App\Components\Planning\Creneau\Dao as _Dao;

/**
 * Classe de test du DAO de créneau de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Dao extends \Api\Tests\Units\App\Libraries\Dao
{
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
