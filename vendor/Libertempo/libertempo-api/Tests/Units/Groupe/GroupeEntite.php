<?php
namespace LibertAPI\Tests\Units\Groupe;

/**
 * Classe de test de l'entité de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 */
final class GroupeEntite extends \LibertAPI\Tests\Units\Tools\Libraries\AEntite
{
    /**
     * @inheritDoc
     */
    public function testConstructWithId()
    {
        $id = 3;
        $comment = 'this is a comment';

        $this->newTestedInstance(['id' => $id, 'name' => 'name', 'comment' => $comment, 'double_validation' => true]);

        $this->assertConstructWithId($this->testedInstance, $id);
        $this->string($this->testedInstance->getName())->isIdenticalTo('name');
        $this->string($this->testedInstance->getComment())->isIdenticalTo($comment);
        $this->boolean($this->testedInstance->isDoubleValidated())->isTrue();
    }

    /**
     * @inheritDoc
     */
    public function testConstructWithoutId()
    {
        $this->newTestedInstance(['comment' => 'a', 'double_validation' => true]);

        $this->variable($this->testedInstance->getId())->isNull();
    }

    /**
     * Teste la méthode populate avec un mauvais domaine de définition
     */
    public function testPopulateBadDomain()
    {
        $this->newTestedInstance([]);
        $data = ['name' => 'name', 'comment' => '', 'double_validation' => 'N'];

        $this->exception(function () use ($data) {
            $this->testedInstance->populate($data);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode populate avec ok
     */
    public function testPopulateOk()
    {
        $this->newTestedInstance([]);
        $data = ['name' => 'name', 'comment' => 'k', 'double_validation' => 'N'];

        $this->testedInstance->populate($data);

        $this->string($this->testedInstance->getName())->isIdenticalTo('name');
        $this->string($this->testedInstance->getComment())->isIdenticalTo('k');
        $this->boolean($this->testedInstance->isDoubleValidated())->isFalse();
    }

    /**
     * @inheritDoc
     */
    public function testReset()
    {
        $this->newTestedInstance(['name' => 'name', 'comment' => 'k', 'double_validation' => 'N']);

        $this->assertReset($this->testedInstance);
    }
}
