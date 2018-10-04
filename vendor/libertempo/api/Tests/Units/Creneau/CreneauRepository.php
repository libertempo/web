<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Planning\Creneau;

/**
 * Classe de test du repository de créneau de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class CreneauRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    public function testGetOneEmpty()
    {
        $this->newTestedInstance($this->connector);
        $this->calling($this->result)->fetch = [];

        $this->exception(function () {
            $this->testedInstance->getOne(4);
        })->isInstanceOf(\RuntimeException::class);
    }

    public function testPutOne()
    {
        $this->newTestedInstance($this->connector);
        $this->calling($this->result)->fetch = [];

        $this->exception(function () {
            $this->testedInstance->putOne(4, []);
        })->isInstanceOf(\RuntimeException::class);
    }

    final protected function getStorageContent() : array
    {
        return [
            'creneau_id' => 42,
            'planning_id' => 12,
            'jour_id' => 7,
            'type_semaine' => 23,
            'type_periode' => 2,
            'debut' => 63,
            'fin' => 55,
        ];
    }

    protected function getConsumerContent() : array
    {
        return [
            'planningId' => 12,
            'jourId' => 4,
            'typeSemaine' => 54,
            'typePeriode' => 191283,
            'debut' => 921,
            'fin' => 2139123,
        ];
    }

    public function testDeleteOne()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->deleteOne(111);
        })->isInstanceOf(\RuntimeException::class);
    }
}
