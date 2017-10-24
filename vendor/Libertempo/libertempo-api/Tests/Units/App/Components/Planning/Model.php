<?php
namespace Tests\Units\App\Components\Planning;

use \App\Components\Planning\Model as _Model;

/**
 * Classe de test du modèle de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Model extends \Tests\Units\App\Libraries\AModel
{
    /**
     * @inheritDoc
     */
    public function testConstructWithId()
    {
        $id = 3;
        $name = 'name';
        $status = 4;

        $model = new _Model(['id' => $id, 'name' => 'name', 'status' => $status]);

        $this->assertConstructWithId($model, $id);
        $this->string($model->getName())->isIdenticalTo($name);
        $this->integer($model->getStatus())->isIdenticalTo($status);
    }

    /**
     * @inheritDoc
     */
    public function testConstructWithoutId()
    {
        $model = new _Model(['name' => 'name', 'status' => 'status']);

        $this->variable($model->getId())->isNull();
    }

    /**
     * Teste la méthode populate avec un mauvais domaine de définition
     */
    public function testPopulateBadDomain()
    {
        $model = new _Model([]);
        $data = ['name' => '', 'status' => 45];

        $this->exception(function () use ($model, $data) {
            $model->populate($data);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode populate avec ok
     */
    public function testPopulateOk()
    {
        $model = new _Model([]);
        $data = ['name' => 'test', 'status' => 48];

        $model->populate($data);

        $this->string($model->getName())->isIdenticalTo($data['name']);
        $this->integer($model->getStatus())->isIdenticalTo($data['status']);
    }

    /**
     * @inheritDoc
     */
    public function testReset()
    {
        $model = new _Model(['id' => 3, 'name' => 'name', 'status' => 'status']);

        $this->assertReset($model);
    }
}
