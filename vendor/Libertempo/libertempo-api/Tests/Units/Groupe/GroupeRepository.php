<?php
namespace LibertAPI\Tests\Units\Groupe;

/**
 * Classe de test du repository de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 */
final class GroupeRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    protected function initDao()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\LibertAPI\Groupe\GroupeDao();
    }

    protected function initEntite()
    {
        $this->entite = new \LibertAPI\Groupe\GroupeEntite([]);
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
        $this->newTestedInstance($this->dao);

        $this->exception(function () {
            $this->testedInstance->deleteOne($this->entite);
        })->isInstanceOf('\LogicException');

    }

    /**
     * Teste la méthode deleteOne tout ok
     */
    public function testDeleteOk()
    {
        $this->dao->getMockController()->delete = 4;
        $this->newTestedInstance($this->dao);

        $this->variable($this->testedInstance->deleteOne($this->entite))->isNull();
    }

    protected function getEntiteContent()
    {
        return [
            'id' => 72,
            'name' => 'name',
            'comment' => 'text',
            'double_validation' => true,
        ];
    }
}
