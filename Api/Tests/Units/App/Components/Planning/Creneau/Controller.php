<?php
namespace Api\Tests\Units\App\Components\Planning\Creneau;

use \Api\App\Components\Planning\Creneau\Controller as _Controller;

/**
 * Classe de test du contrôleur de créneau de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Controller extends \Api\Tests\Units\App\Libraries\AController
{
    /**
     * @var \mock\Api\App\Components\Planning\Creneau\Repository Mock du repository associé
     */
    private $repository;

    /**
     * @var \mock\Api\App\Components\Planning\Creneau\Model Mock du modèle associé
     */
    private $model;

    /**
     * Init des tests
     */
    public function beforeTestMethod($method) {
        parent::beforeTestMethod($method);
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->repository = new \mock\Api\App\Components\Planning\Creneau\Repository();
        $this->mockGenerator->orphanize('__construct');
        $this->model = new \mock\Api\App\Components\Planning\Creneau\Model();
        $this->model->getMockController()->getId = 42;
        $this->model->getMockController()->getPlanningId = 12;
        $this->model->getMockController()->getJourId = 12;
        $this->model->getMockController()->getTypeSemaine = 12;
        $this->model->getMockController()->getTypePeriode = 12;
        $this->model->getMockController()->getDebut = 12;
        $this->model->getMockController()->getFin = 12;
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Teste la méthode get d'un détail trouvé
     */
    public function testGetOneFound()
    {
        $this->repository->getMockController()->getOne = $this->model;
        $controller = new _Controller($this->repository, $this->router);

        $response = $controller->get($this->request, $this->response, ['creneauId' => 99, 'planningId' => 45]);
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            ->string['message']->isIdenticalTo('')
            ->array['data']->isNotEmpty()
        ;
    }

    /**
    * Teste la méthode get d'un détail non trouvé
     */
    public function testGetOneNotFound()
    {
        $this->repository->getMockController()->getOne = function () {
            throw new \DomainException('');
        };
        $controller = new _Controller($this->repository, $this->router);

        $response = $controller->get($this->request, $this->response, ['creneauId' => 99, 'planningId' => 45]);

        $this->assertError($response, 404);
    }

    /**
     * Teste le fallback de la méthode get d'un détail
     */
    public function testGetOneFallback()
    {
        $this->repository->getMockController()->getOne = function () {
            throw new \Exception('');
        };
        $controller = new _Controller($this->repository, $this->router);

        $this->exception(function () use ($controller) {
            $controller->get($this->request, $this->response, ['creneauId' => 99, 'planningId' => 45]);
        })->isInstanceOf('\Exception');
    }

    /**
    * Teste la méthode get d'une liste trouvée
     */
    public function testGetListFound()
    {
        $this->repository->getMockController()->getList = [
            42 => $this->model,
        ];
        $controller = new _Controller($this->repository, $this->router);

        $response = $controller->get($this->request, $this->response, ['planningId' => 45]);
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            ->string['message']->isIdenticalTo('')
            //->array['data']->hasSize(1) // l'asserter atoum en sucre syntaxique est buggé, faire un ticket
        ;
        $this->array($data['data'][0])->hasKey('id');
    }

    /**
    * Teste la méthode get d'une liste non trouvée
     */
    public function testGetListNotFound()
    {
        $this->repository->getMockController()->getList = function () {
            throw new \UnexpectedValueException('');
        };
        $controller = new _Controller($this->repository, $this->router);

        $response = $controller->get($this->request, $this->response, ['planningId' => 45]);

        $this->assertError($response, 404);
    }

    /**
     * Teste le fallback de la méthode get d'une liste
     */
    public function testGetListFallback()
    {
        $this->repository->getMockController()->getList = function () {
            throw new \Exception('');
        };
        $controller = new _Controller($this->repository, $this->router);

        $this->exception(function () use ($controller) {
            $controller->get($this->request, $this->response, ['planningId' => 45]);
        })->isInstanceOf('\Exception');
    }
}
