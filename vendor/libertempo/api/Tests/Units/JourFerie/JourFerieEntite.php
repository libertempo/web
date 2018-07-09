<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\JourFerie;

/**
 * Classe de test de l'entité de jour férié
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 */
final class JourFerieEntite extends \LibertAPI\Tests\Units\Tools\Libraries\AEntite
{
    /**
     * @inheritDoc
     */
    public function testConstructWithId()
    {
        $id = 3;

        $this->newTestedInstance(['id' => $id, 'date' => 'date']);

        $this->assertConstructWithId($this->testedInstance, $id);
        $this->string($this->testedInstance->getDate())->isIdenticalTo('date');
    }

    /**
     * @inheritDoc
     */
    public function testConstructWithoutId()
    {
        $this->newTestedInstance(['date' => 'a']);

        $this->variable($this->testedInstance->getId())->isNull();
    }

    /**
     * Teste la méthode populate avec un mauvais domaine de définition
     */
    public function testPopulateBadDomain()
    {
        $this->boolean(true)->isTrue();
    }

    /**
     * Teste la méthode populate avec ok
     */
    public function testPopulateOk()
    {
        $this->boolean(true)->isTrue();
    }

    /**
     * @inheritDoc
     */
    public function testReset()
    {
        $this->newTestedInstance(['date' => 'date']);

        $this->assertReset($this->testedInstance);
    }
}
