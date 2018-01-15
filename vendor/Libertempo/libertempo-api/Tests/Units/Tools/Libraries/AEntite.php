<?php
namespace LibertAPI\Tests\Units\Tools\Libraries;

use LibertAPI\Tools\Libraries\AEntite as _AEntite;

/**
 * Classe commune de test sur les entités
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
abstract class AEntite extends \Atoum
{
    /**
     * @var \Tools\Libraries\AEntite $entite Entité en cours de test
     */
    protected $entite;

    /**
    * Teste la méthode __construct avec un Id (typiquement lors d'un get())
     */
    abstract public function testConstructWithId();

    /**
     * Teste la méthode __construct sans Id (typiquement lors d'un post())
     */
    abstract public function testConstructWithoutId();

    /**
     * Asserters commun sur la construction avec id
     *
     * @param _AEntite $entite
     * @param int $id Id de l'objet
     */
    final protected function assertConstructWithId(_AEntite $entite, $id)
    {
        $this->integer($entite->getId())->isIdenticalTo($id);
    }

    /**
     * Teste la méthode reset
     */
    abstract public function testReset();

    /**
     * Asserteurs communs sur le reset
     */
    final protected function assertReset(_AEntite $entite)
    {
        $entite->reset();

        $this->variable($entite->getId())->isNull();
    }
}
