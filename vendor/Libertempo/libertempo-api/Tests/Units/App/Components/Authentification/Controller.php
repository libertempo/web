<?php
namespace Tests\Units\App\Components\Authentification;

use \App\Components\Authentification\Controller as _Controller;

/**
 * Classe de test du contrôleur de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class Controller extends \Tests\Units\App\Libraries\AController
{
    /**
     * @var \mock\App\Components\Utilisateur\Repository Mock du repository associé
     */
    private $repository;

    /**
     * @var \mock\App\Components\Utilisateur\Model Mock du modèle associé
     */
    private $model;

    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->repository = new \mock\App\Components\Utilisateur\Repository();
        $this->mockGenerator->orphanize('__construct');
        $this->model = new \mock\App\Components\Utilisateur\Model();
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Teste la méthode avec un mauvais header
     */
    public function testGetBadAuthentificationMechanism()
    {
        // Le framework fait du traitement, un mauvais json est simplement null
        $this->request->getMockController()->getHeaderLine = 'NotBasic';
        $controller = new _Controller($this->repository, $this->router);

        $response = $controller->get($this->request, $this->response);

        $this->assertError($response, 400);
    }

    /**
     * Teste la méthode get d'une authentification non réussie
     */
    public function testGetNotFound()
    {
        $this->repository->getMockController()->find = function () {
            throw new \UnexpectedValueException('');
        };
        $this->request->getMockController()->getHeaderLine = 'Basic QWxhZGRpbjpPcGVuU2VzYW1l';
        $controller = new _Controller($this->repository, $this->router);

        $response = $controller->get(
            $this->request,
            $this->response
        );

        $this->assertError($response, 404);
    }

    /**
     * Teste la méthode get d'une authentification réussie
     */
    public function testGetFound()
    {
        $token = 'abcde';
        $this->model->getMockController()->getToken = $token;
        $this->repository->getMockController()->find = $this->model;
        $this->repository->getMockController()->regenerateToken = $this->model;
        $this->request->getMockController()->getHeaderLine = 'Basic QWxhZGRpbjpPcGVuU2VzYW1l';
        $controller = new _Controller($this->repository, $this->router);

        $response = $controller->get($this->request, $this->response);
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            ->string['message']->isIdenticalTo('')
            ->string['data']->isIdenticalTo($token)
        ;
    }
}
