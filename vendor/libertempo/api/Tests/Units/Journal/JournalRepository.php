<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Journal;

/**
 * Classe de test du repository de journal
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class JournalRepository extends \LibertAPI\Tests\Units\Tools\Libraries\ARepository
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
            'log_id' => 81,
            'log_p_num' => 1213,
            'log_user_login_par' => 'Baloo',
            'log_user_login_pour' => 'Mowgli',
            'log_etat' => 'gere',
            'log_comment' => 'nope',
            'log_date' => '2017-12-01',
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
            $this->testedInstance->putOne(8712, []);
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
            $this->testedInstance->deleteOne(723);
        })->isInstanceOf(\RuntimeException::class);
    }
}
