<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Groupe\GrandResponsable;

/**
 * Classe de test du repository de grand responsable de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 */
final class GrandResponsableRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
{
    public function testGetOneEmpty()
    {
        $this->newTestedInstance($this->connector);
        $this->exception(function () {
            $this->testedInstance->getOne(4);
        })->isInstanceOf(\RuntimeException::class);
    }

    final protected function getStorageContent() : array
    {
        return [
            'id' => 'Baloo',
            'ggr_gid' => '8',
            'ggr_login' => 'Hurricane',
        ];
    }

    public function testPostOne()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->postOne($this->getConsumerContent());
        })->isInstanceOf(\RuntimeException::class);
    }

    public function testPutOne()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->putOne(123, []);
        })->isInstanceOf(\RuntimeException::class);
    }

    protected function getConsumerContent() : array
    {
        return [
        ];
    }

    public function testDeleteOne()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->deleteOne(2182);
        })->isInstanceOf(\RuntimeException::class);
    }
}
