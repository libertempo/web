<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Planning;

use \LibertAPI\Planning\PlanningRepository as _Repository;

/**
 * Classe de test du repository de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class PlanningRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    protected function initDao()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\LibertAPI\Planning\PlanningDao();
    }

    protected function initEntite()
    {
        $this->entite = new \LibertAPI\Planning\PlanningEntite(['id' => 42]);
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Teste le fallback de la méthode deleteOne
     */
    public function testDeleteFallback()
    {
        $this->dao->getMockController()->delete = function () {
            throw new \LogicException('');
        };
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->deleteOne($this->entite);
        })->isInstanceOf('\LogicException');
    }

    /**
     * Teste la méthode deleteOne tout ok
     */
    public function testDeleteOk()
    {
        $this->dao->getMockController()->delete = 4;
        $repository = new _Repository($this->dao);

        $this->variable($repository->deleteOne($this->entite))->isNull();
    }

    protected function getEntiteContent()
    {
        return [
            'id' => 72,
            'name' => 'name',
            'status' => 59,
        ];
    }
}
