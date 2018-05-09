<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Absence\Type;

/**
 * Classe de test de l'entité de type d'absence
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class TypeEntite extends \LibertAPI\Tests\Units\Tools\Libraries\AEntite
{
    /**
     * @inheritDoc
     */
    public function testConstructWithId()
    {
        $id = 3;
        $type = 'type';
        $libelle = 'douze';

        $this->newTestedInstance(['id' => $id, 'type' => $type, 'libelle' => $libelle]);

        $this->assertConstructWithId($this->testedInstance, $id);
        $this->string($this->testedInstance->getType())->isIdenticalTo($type);
        $this->string($this->testedInstance->getLibelle())->isIdenticalTo($libelle);
    }

    /**
     * @inheritDoc
     */
    public function testConstructWithoutId()
    {
        $this->newTestedInstance(['name' => 'name', 'status' => 'status']);

        $this->variable($this->testedInstance->getId())->isNull();
    }

    /**
     * Teste la méthode populate avec un mauvais domaine de définition
     */
    public function testPopulateBadDomain()
    {
        $this->newTestedInstance([]);
        $data = ['type' => '', 'libelle' => '45', 'libelleCourt' => 'non'];

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
        $data = ['type' => 'a', 'libelle' => '45', 'libelleCourt' => 'oui'];

        $this->testedInstance->populate($data);

        $this->string($this->testedInstance->getType())->isIdenticalTo($data['type']);
        $this->string($this->testedInstance->getLibelle())->isIdenticalTo($data['libelle']);
    }

    /**
     * @inheritDoc
     */
    public function testReset()
    {
        $this->newTestedInstance(['id' => 3, 'name' => 'name', 'status' => 'status']);

        $this->assertReset($this->testedInstance);
    }
}
