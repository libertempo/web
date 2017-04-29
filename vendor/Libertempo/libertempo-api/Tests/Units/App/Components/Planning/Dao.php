<?php
namespace Tests\Units\App\Components\Planning;

use \App\Components\Planning\Dao as _Dao;

/**
 * Classe de test du DAO de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Dao extends \Tests\Units\App\Libraries\ADao
{
    /*************************************************
     * GET
     *************************************************/

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

    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode post quand tout est ok
     */
    public function testPostOk()
    {
        $this->connector->getMockController()->lastInsertId = 314;
        $dao = new _Dao($this->connector);

        $postId = $dao->post([
            'name' => 'name',
            'status' => 59,
        ]);

        $this->integer($postId)->isIdenticalTo(314);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Teste la méthode put quand tout est ok
     */
    public function testPutOk()
    {
        $dao = new _Dao($this->connector);

        $put = $dao->put([
            'name' => 'name',
            'status' => 59,
        ], 12);

        $this->variable($put)->isNull();
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Teste la méthode delete quand tout est ok
     */
    public function testDeleteOk()
    {
        $this->statement->getMockController()->rowCount = 1;
        $dao = new _Dao($this->connector);

        $res = $dao->delete(7);

        $this->integer($res)->isIdenticalTo(1);
    }
}
