<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Groupe\Employe;

/**
 * Classe de test du repository d'employé de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 */
final class EmployeRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
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
            'id' => 'Aladdin',
            'gu_gid' => '8',
            'gu_login' => 'Teddy',
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
            $this->testedInstance->putOne(1, []);
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
            $this->testedInstance->deleteOne(91823);
        })->isInstanceOf(\RuntimeException::class);
    }
}
