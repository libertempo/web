<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\JourFerie;

/**
 * Classe de test du repository de jour férié
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 */
final class JourFerieRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
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
            'id' => uniqid(),
            'jf_date' => '2018-05-14',
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
            $this->testedInstance->putOne(81273, []);
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
            $this->testedInstance->deleteOne(518);
        })->isInstanceOf(\RuntimeException::class);
    }
}
