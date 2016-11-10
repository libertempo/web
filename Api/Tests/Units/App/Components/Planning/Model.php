<?php
namespace Api\Tests\Units\App\Components\Planning;

use \Api\App\Components\Planning\Model as _Model;

/**
 * Classe de test du modèle de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Model extends \Atoum
{
    /**
     * Teste la méthode __construct avec un Id (typiquement lors d'un get())
     */
    public function testConstructWithId()
    {
        $id = 3;
        $name = 'name';
        $status = 4;

        $model = new _Model(['id' => $id, 'name' => 'name', 'status' => $status]);

        $this->integer($model->getId())->isIdenticalTo($id);
        $this->string($model->getName())->isIdenticalTo($name);
        $this->integer($model->getStatus())->isIdenticalTo($status);
    }

    /**
     * Teste la méthode __construct sans Id (typiquement lors d'un post())
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
        $name = 'test';
        $status = 48;
        $model = new _Model([]);
        $data = ['name' => $name, 'status' => $status];

        $model->populate($data);

        $this->string($model->getName())->isIdenticalTo($name);
        $this->integer($model->getStatus())->isIdenticalTo($status);
    }
}
