<?php
namespace Tests\Units\App\Components\Planning\Creneau;

use \App\Components\Planning\Creneau\Repository as _Repository;

/**
 * Classe de test du repository de créneau de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Repository extends \Atoum
{
    /**
     * @var \mock\App\Components\Planning\Creneau\Dao Mock du DAO du créneau
     */
    private $dao;

    /**
     * @var \mock\App\Components\Planning\Creneau\Model Mock du Modèle de créneau
     */
    private $model;

    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->dao = new \mock\App\Components\Planning\Creneau\Dao();
        $this->mockGenerator->orphanize('__construct');
        $this->model = new \mock\App\Components\Planning\Creneau\Model();
        $this->model->getMockController()->getId = 42;
    }

    /*************************************************
    * GET
    *************************************************/

    /**
     * Teste la méthode getOne avec un id non trouvé
     */
    public function testGetOneNotFound()
    {
        $this->dao->getMockController()->getById = [];
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->getOne(99, 23);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode getOne avec un id trouvé
     */
    public function testGetOneFound()
    {
        $this->dao->getMockController()->getById = [
            'creneau_id' => '42',
            'planning_id' => 99,
            'jour_id' => 99,
            'type_semaine' => 99,
            'type_periode' => 99,
            'debut' => 99,
            'fin' => 99,
        ];
        $repository = new _Repository($this->dao);

        $model = $repository->getOne(42, 23);

        $this->object($model)->isInstanceOf('\App\Libraries\AModel');
        $this->integer($model->getId())->isIdenticalTo(42);
    }

    /**
     * Teste la méthode getList avec des critères non pertinents
     */
    public function testGetListNotFound()
    {
        $this->dao->getMockController()->getList = [];
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->getList(['planningId' => 58]);
        })->isInstanceOf('\UnexpectedValueException');
    }

    /**
     * Teste la méthode getList avec des critères pertinents
     */
    public function testGetListFound()
    {
        $this->dao->getMockController()->getList = [[
            'creneau_id' => '42',
            'planning_id' => 99,
            'jour_id' => 99,
            'type_semaine' => 99,
            'type_periode' => 99,
            'debut' => 99,
            'fin' => 99,
        ]];
        $repository = new _Repository($this->dao);

        $models = $repository->getList(['planningId' => 53]);

        $this->array($models)->hasKey(42);
        $this->object($models[42])->isInstanceOf('\App\Libraries\AModel');
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode postList avec un champ manquant
     */
    public function testPostListException()
    {
        $repository = new _Repository($this->dao);
        $model = new \mock\App\Components\Planning\Creneau\Model([]);
        $model->getMockController()->populate = function () {
            throw new \LogicException('');
        };
        $data = [
            'planningId' => 34,
            'jourId' => 23,
            'typeSemaine' => 15,
            'typePeriode' => 57,
            'debut' => 83,
            'fin' => 92,
        ];

        $this->exception(function () use ($repository, $data, $model) {
            $repository->postList([$data], $model);
        })->isInstanceOf('\LogicException');
    }

    /**
     * Teste la méthode postList tout ok
     */
    public function testPostListOk()
    {
        $repository = new _Repository($this->dao);
        $model = new \mock\App\Components\Planning\Creneau\Model([]);
        $model->getMockController()->populate = '';
        $model->getMockController()->getPlanningId = 3;
        $model->getMockController()->getJourId = 4;
        $model->getMockController()->getTypeSemaine = 5;
        $model->getMockController()->getTypePeriode = 6;
        $model->getMockController()->getDebut = 7;
        $model->getMockController()->getFin = 8;
        $data = [
            [
                'planningId' => 34,
                'jourId' => 6,
                'typeSemaine' => 2,
                'typePeriode' => 1,
                'debut' => 13,
                'fin' => 2,
            ]
        ];
        $this->dao->getMockController()->post[1] = 3;
        $this->dao->getMockController()->post[2] = 9;


        $post = $repository->postList($data, $model);

        foreach ($post as $postId) {
            $this->integer($postId);
        }
    }

    /**
     * Teste la méthode postOne avec un champ manquant
     */
    public function testPostOneMissingArgument()
    {
        $repository = new _Repository($this->dao);
        $model = new \mock\App\Components\Planning\Creneau\Model([]);

        $this->exception(function () use ($repository, $model) {
            $repository->postOne([], $model);
        })->isInstanceOf('\App\Exceptions\MissingArgumentException');
    }

    /**
     * Teste la méthode postOne avec un champ incohérent
     */
    public function testPostOneBadDomain()
    {
        $repository = new _Repository($this->dao);
        $model = new \mock\App\Components\Planning\Creneau\Model([]);
        $model->getMockController()->populate = function () {
            throw new \DomainException('');
        };
        $data = [
            'planningId' => 34,
            'jourId' => 23,
            'typeSemaine' => 15,
            'typePeriode' => 57,
            'debut' => 83,
            'fin' => 92,
        ];

        $this->exception(function () use ($repository, $data, $model) {
            $repository->postOne($data, $model);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode postOne tout ok
     */
    public function testPostOneOk()
    {
        $repository = new _Repository($this->dao);
        $model = new \mock\App\Components\Planning\Creneau\Model([]);
        $model->getMockController()->populate = '';
        $model->getMockController()->getPlanningId = 3;
        $model->getMockController()->getJourId = 4;
        $model->getMockController()->getTypeSemaine = 5;
        $model->getMockController()->getTypePeriode = 6;
        $model->getMockController()->getDebut = 7;
        $model->getMockController()->getFin = 8;
        $data = [
            'planningId' => 34,
            'jourId' => 2,
            'typeSemaine' => 0,
            'typePeriode' => 2,
            'debut' => 83,
            'fin' => 92,
        ];
        $this->dao->getMockController()->post = 3;

        $post = $repository->postOne($data, $model);

        $this->integer($post);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Teste la méthode putOne avec un champ manquant
     */
    public function testPutOneMissingArgument()
    {
        $repository = new _Repository($this->dao);

        $this->exception(function () use ($repository) {
            $repository->putOne(['planningId' => 4], new \mock\App\Components\Planning\Creneau\Model([]));
        })->isInstanceOf('\App\Exceptions\MissingArgumentException');
    }

    /**
     * Teste la méthode putOne avec un champ incohérent
     */
    public function testPutOneBadDomain()
    {
        $repository = new _Repository($this->dao);
        $model = new \mock\App\Components\Planning\Creneau\Model([]);
        $model->getMockController()->populate = function () {
            throw new \DomainException('');
        };

        $this->exception(function () use ($repository, $model) {
            $repository->putOne(
                [
                    'planningId' => 4,
                    'jourId' => 9,
                    'typeSemaine' => 3,
                    'typePeriode' => 4,
                    'debut' => 3,
                    'fin' => 129,
                ],
                $model);
        })->isInstanceOf('\DomainException');
    }

    /**
     * Teste la méthode putOne tout ok
     */
    public function testPutOneOk()
    {
        $repository = new _Repository($this->dao);

        $result = $repository->putOne(
            [
                'planningId' => 8,
                'jourId' => 19,
                'typeSemaine' => 5,
                'typePeriode' => 8,
                'debut' => 37,
                'fin' => 129,
            ],
            $this->model);

        $this->variable($result)->isNull();
    }

    /**
     * En train de faire la méthode put du dao du creneau (au passage il y a aura post à faire)
     */
}
