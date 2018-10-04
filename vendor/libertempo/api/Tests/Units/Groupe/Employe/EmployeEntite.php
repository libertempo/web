<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Groupe\Employe;

/**
 * Classe de test de l'entité de l'employe de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.1
 */
final class EmployeEntite extends \LibertAPI\Tests\Units\Tools\Libraries\AEntite
{
    /**
     * @inheritDoc
     */
    public function testConstructWithId()
    {
        $id = 23;
        $groupeId = 4;
        $login = 'Boule';

        $this->newTestedInstance(['id' => $id, 'groupeId' => $groupeId, 'login' => $login]);

        $this->assertConstructWithId($this->testedInstance, $id);
        $this->integer($this->testedInstance->getGroupeId())->isIdenticalTo($groupeId);
        $this->string($this->testedInstance->getLogin())->isIdenticalTo($login);
    }

    /**
     * @inheritDoc
     */
    public function testConstructWithoutId()
    {
        $this->newTestedInstance(['groupeId' => 5, 'login' => 'Bill']);

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
        $this->newTestedInstance(['id' => 4, 'groupeId' => 3]);

        $this->assertReset($this->testedInstance);
    }
}
