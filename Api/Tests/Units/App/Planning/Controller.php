<?php
namespace Api\Tests\Units\App\Planning;

use \Api\App\Planning\Controller as _Controller;

/**
 * Classe de test du contrôleur de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Controller extends \Atoum
{
    /**
     * @var \mock\Slim\Http\Request Mock de la requête HTTP
     */
    private $request;

    /**
     * @var \mock\Slim\\Http\Response Mock de la réponse HTTP
     */
    private $response;

    /**
     * @var \mock\Api\App\Planning\Repository Mock du repository associé
     */
    private $repository;

    /**
     * @var \mock\Api\App\Utilisateur\Repository Mock du repository utilisateur
     */
    private $utilisateurRepository;

    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->request = new \mock\Slim\Http\Request();
        $this->response = new \mock\Slim\Http\Response();
        $this->mockGenerator->orphanize('__construct');
        $this->repository = new \mock\Api\App\Planning\Repository();
        $this->utilisateurRepository = new \mock\Api\App\Planning\Repository();
    }

    /**
     * Teste les méthodes Http du contrat du contrôleur
     */
    public function testGetAvailablesMethods()
    {
        $controller = new _Controller($this->request, $this->response, $this->repository, $this->utilisateurRepository);

        $availables = $controller->getAvailablesMethods();

        $this->array($availables)->isIdenticalTo(['get']);
    }

    /**
     * Teste le nom de la ressource
     */
    public function testGetRessourceName()
    {
        $controller = new _Controller($this->request, $this->response, $this->repository, $this->utilisateurRepository);

        $resourceName = $controller->getResourceName();

        $this->string($resourceName)->isIdenticalTo('plannings');
    }

    /**
     * Teste la méthode get d'un détail trouvé
     */
    public function testGetOneFound()
    {
        $this->repository->getMockController()->getOne = function () {
            return new \Api\App\Planning\Model(32, []);
        };
        $controller = new _Controller($this->request, $this->response, $this->repository, $this->utilisateurRepository);

        $response = $controller->get(99);
        $data = json_decode((string) $response->getBody(), true);

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
        $controller = new _Controller($this->request, $this->response, $this->repository, $this->utilisateurRepository);

        $response = $controller->get(99);
        $data = json_decode((string) $response->getBody(), true);

        $this->integer($response->getStatusCode())->isIdenticalTo(404);
        $this->array($data)
            ->integer['code']->isIdenticalTo(404)
            ->string['status']->isIdenticalTo('error')
            ->string['message']->isNotEqualTo('')
            ->array['data']->isNotEmpty()
        ;
    }

    /**
    * Teste la méthode get d'une liste trouvée
     */
    public function testGetListFound()
    {
        $this->request->getMockController()->getQueryParams = [];
        $this->repository->getMockController()->getList = ['a'];
        $controller = new _Controller($this->request, $this->response, $this->repository, $this->utilisateurRepository);

        $response = $controller->get();
        $data = json_decode((string) $response->getBody(), true);

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            ->string['message']->isIdenticalTo('')
            ->array['data']->isNotEmpty()
        ;
    }

    /**
    * Teste la méthode get d'une liste non trouvée
     */
    public function testGetListNotFound()
    {
        $this->request->getMockController()->getQueryParams = [];
        $this->repository->getMockController()->getList = [];
        $controller = new _Controller($this->request, $this->response, $this->repository, $this->utilisateurRepository);

        $response = $controller->get();
        $data = json_decode((string) $response->getBody(), true);

        $this->integer($response->getStatusCode())->isIdenticalTo(404);
        $this->array($data)
            ->integer['code']->isIdenticalTo(404)
            ->string['status']->isIdenticalTo('error')
            ->string['message']->isNotEqualTo('')
            ->array['data']->isNotEmpty()
        ;
    }
}
