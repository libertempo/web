<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Absence\Type;

/**
 * Classe de test du repository de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class TypeRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    protected function initDao()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\LibertAPI\Absence\Type\TypeDao();
    }

    protected function initEntite()
    {
        $this->entite = new \LibertAPI\Absence\Type\TypeEntite([]);
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
            $this->testedInstance->deleteOne(new \LibertAPI\Absence\Type\TypeEntite(['id' => 49]));
        })->isInstanceOf('\LogicException');
    }

    /**
     * Teste la méthode deleteOne tout ok
     */
    public function testDeleteOk()
    {
        $this->dao->getMockController()->delete = 4;
        $this->newTestedInstance($this->dao);
        $entite = new \LibertAPI\Absence\Type\TypeEntite(['id' => 49]);

        $this->variable($this->testedInstance->deleteOne($entite))->isNull();
    }

    protected function getEntiteContent()
    {
        return [
            'id' => 87,
            'type' => 'quatre',
            'libelle' => 'chipolata',
            'libelleCourt' => 'cp',
        ];
    }
}
