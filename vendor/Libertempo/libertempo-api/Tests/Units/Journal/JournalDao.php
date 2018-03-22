<?php
namespace LibertAPI\Tests\Units\Journal;

/**
 * Classe de test du DAO de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class JournalDao extends \LibertAPI\Tests\Units\Tools\Libraries\ADao
{
    /*************************************************
     * GET
     *************************************************/

    /**
    * Teste la méthode getById avec un id non trouvé
    */
    public function testGetByIdNotFound()
    {
        $this->exception(function () {
            $this->newTestedInstance($this->connector)->getById(0);
        });
    }

    /**
    * Teste la méthode getById avec un id trouvé
    */
    public function testGetByIdFound()
    {
        $this->exception(function () {
            $this->newTestedInstance($this->connector)->getById(0);
        });
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode post quand tout est ok
     */
    public function testPostOk()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->delete([]);
        })->isInstanceOf(\RuntimeException::class);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Teste la méthode put quand tout est ok
     */
    public function testPutOk()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->delete([]);
        })->isInstanceOf(\RuntimeException::class);
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Teste la méthode delete
     */
    public function testDeleteOk()
    {
        $this->newTestedInstance($this->connector);

        $this->exception(function () {
            $this->testedInstance->delete([]);
        })->isInstanceOf(\RuntimeException::class);
    }

    protected function getStorageContent()
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
}
