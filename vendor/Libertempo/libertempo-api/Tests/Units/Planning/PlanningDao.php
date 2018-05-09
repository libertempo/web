<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Planning;

use LibertAPI\Planning\PlanningDao as _Dao;

use LibertAPI\Planning\PlanningEntite;

/**
 * Classe de test du DAO de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class PlanningDao extends \LibertAPI\Tests\Units\Tools\Libraries\ADao
{
    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode post quand tout est ok
     */
    public function testPostOk()
    {
        $this->calling($this->connector)->lastInsertId = 314;
        $dao = new _Dao($this->connector);

        $postId = $dao->post(new PlanningEntite($this->entiteContent));

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

        $put = $dao->put(new PlanningEntite($this->entiteContent));

        $this->variable($put)->isNull();
    }

    protected function getStorageContent()
    {
        return [
            'planning_id' => 42,
            'name' => 'name',
            'status' => 59,
        ];
    }

    private $entiteContent = [
        'id' => 72,
        'name' => 'name',
        'status' => 59,
    ];
}
