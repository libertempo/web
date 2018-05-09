<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Tools\Libraries;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * Classe de test des repositories
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.6
 */
abstract class ARepository extends \Atoum
{
    /**
     * @var \LibertAPI\Tools\Libraries\ADao
     */
    protected $dao;

    /**
     * @var \LibertAPI\Tools\Libraries\AEntite
     */
    protected $entite;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->initDao();
        $this->initEntite();
    }

    abstract protected function initDao();

    abstract protected function initEntite();

    /*************************************************
     * GET
     *************************************************/

    /**
     * Teste la méthode getOne
     */
    public function testGetOne()
    {
        $this->dao->getMockController()->getById = $this->entite;
        $repository = $this->newTestedInstance($this->dao);

        $entite = $repository->getOne(42);

        $this->object($entite)->isInstanceOf(AEntite::class);
    }

    /**
     * Teste la méthode getList
     */
    public function testGetList()
    {
        $this->calling($this->dao)->getList = [42 => $this->entite];
        $repository = $this->newTestedInstance($this->dao);

        $entites = $repository->getList([]);

        $this->array($entites)->hasKey(42);
        $this->object($entites[42]);
    }

    /**
     * Teste la méthode postOne
     */
    public function testPostOne()
    {
        $repository = $this->newTestedInstance($this->dao);
        $this->calling($this->dao)->post = 768;

        $post = $repository->postOne($this->getEntiteContent(), $this->entite);

        $this->integer($post)->isIdenticalTo(768);
    }

    /**
     * Teste la méthode putOne
     */
    public function testPutOne()
    {
        $repository = $this->newTestedInstance($this->dao);

        $put = $repository->putOne($this->getEntiteContent(), $this->entite);

        $this->variable($put)->isNull();
    }

    abstract protected function getEntiteContent();
}
