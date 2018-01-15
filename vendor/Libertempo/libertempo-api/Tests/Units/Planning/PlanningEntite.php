<?php
namespace LibertAPI\Tests\Units\Planning;

use \LibertAPI\Planning\PlanningEntite as _Entite;

/**
 * Classe de test du modèle de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class PlanningEntite extends \LibertAPI\Tests\Units\Tools\Libraries\AEntite
{
    /**
     * @inheritDoc
     */
    public function testConstructWithId()
    {
        $id = 3;
        $name = 'name';
        $status = 4;

        $entite = new _Entite(['id' => $id, 'name' => 'name', 'status' => $status]);

        $this->assertConstructWithId($entite, $id);
        $this->string($entite->getName())->isIdenticalTo($name);
        $this->integer($entite->getStatus())->isIdenticalTo($status);
    }

    /**
     * @inheritDoc
     */
    public function testConstructWithoutId()
    {
        $entite = new _Entite(['name' => 'name', 'status' => 'status']);

        $this->variable($entite->getId())->isNull();
    }

    /**
     * Teste la méthode populate avec un mauvais domaine de définition
     */
    public function testPopulateBadDomain()
    {
        $entite = new _Entite([]);
        $data = ['name' => '', 'status' => 45];

        $this->exception(function () use ($entite, $data) {
            $entite->populate($data);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode populate avec ok
     */
    public function testPopulateOk()
    {
        $entite = new _Entite([]);
        $data = ['name' => 'test', 'status' => 48];

        $entite->populate($data);

        $this->string($entite->getName())->isIdenticalTo($data['name']);
        $this->integer($entite->getStatus())->isIdenticalTo($data['status']);
    }

    /**
     * @inheritDoc
     */
    public function testReset()
    {
        $entite = new _Entite(['id' => 3, 'name' => 'name', 'status' => 'status']);

        $this->assertReset($entite);
    }
}
