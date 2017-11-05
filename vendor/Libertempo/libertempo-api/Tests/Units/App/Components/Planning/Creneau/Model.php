<?php
namespace Tests\Units\App\Components\Planning\Creneau;

use \App\Components\Planning\Creneau\Model as _Model;

/**
 * Classe de test du modèle de créneau
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
        $planning = 12;

        $model = new _Model(['id' => $id, 'planningId' => $planning]);

        $this->assertConstructWithId($model, $id);
        $this->integer($model->getPlanningId())->isIdenticalTo($planning);
    }

    /**
     * Teste la méthode __construct sans Id (typiquement lors d'un post())
     */
    public function testConstructWithoutId()
    {
        $model = new _Model(['planningId' => 34]);

        $this->variable($model->getId())->isNull();
    }

    /**
     * Teste la méthode populate avec un mauvais domaine de définition
     */
    public function testPopulateBadDomain()
    {
        $model = new _Model([]);
        $data = [
            'planningId' => '',
            'typeSemaine' => '',
            'typePeriode' => '',
            'jourId' => '',
            'debut' => '',
            'fin' => ''
        ];

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
        $data = [
            'planningId' => 12,
            'typeSemaine' => 23,
            'typePeriode' => 34,
            'jourId' => 45,
            'debut' => 56,
            'fin' => 67,
        ];

        $model->populate($data);

        $this->integer($model->getPlanningId())->isIdenticalTo($data['planningId']);
        $this->integer($model->getTypeSemaine())->isIdenticalTo($data['typeSemaine']);
        $this->integer($model->getTypePeriode())->isIdenticalTo($data['typePeriode']);
        $this->integer($model->getJourId())->isIdenticalTo($data['jourId']);
        $this->integer($model->getDebut())->isIdenticalTo($data['debut']);
        $this->integer($model->getFin())->isIdenticalTo($data['fin']);
    }

    /**
     * @inheritDoc
     */
    public function testReset()
    {
        $model = new _Model(['id' => 39, 'planningId' => 'test']);

        $this->assertReset($model);
    }

}
