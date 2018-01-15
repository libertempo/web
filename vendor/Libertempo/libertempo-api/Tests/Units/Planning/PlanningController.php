<?php
namespace LibertAPI\Tests\Units\Planning;

/**
 * Classe de test du contrôleur de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class PlanningController extends \LibertAPI\Tests\Units\Tools\Libraries\ARestController
{
    /**
     * {@inheritdoc}
     */
    protected function initRepository()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->repository = new \mock\LibertAPI\Planning\PlanningRepository();
    }

    /**
     * {@inheritdoc}
     */
    protected function initEntite()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->entite = new \mock\LibertAPI\Planning\PlanningEntite();
        $this->entite->getMockController()->getId = 42;
        $this->entite->getMockController()->getName = 12;
        $this->entite->getMockController()->getStatus = 12;
    }

    /**
     * Teste la méthode get d'une liste avec des droits insuffisants
     */
    public function testGetListMissingRight()
    {
        $this->mockGenerator->orphanize('__construct');
        $uri = new \mock\Slim\Http\Uri();
        $this->calling($uri)->getPath = '';
        $this->calling($this->request)->getQueryParams = [];
        $this->calling($this->request)->getUri = $uri;
        $this->calling($this->repository)->getList = function () {
            throw new \LibertAPI\Tools\Exceptions\MissingRightException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->getList();

        $this->assertFail($response, 403);
    }

    protected function getOne()
    {
        return $this->testedInstance->get($this->request, $this->response, ['planningId' => 99]);
    }

    protected function getList()
    {
        return $this->testedInstance->get($this->request, $this->response, []);
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * Teste la méthode post d'un json mal formé
     */
    public function testPostJsonBadFormat()
    {
        // Le framework fait du traitement, un mauvais json est simplement null
        $this->request->getMockController()->getParsedBody = null;
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response);

        $this->assertFail($response, 400);
    }

    /**
     * Teste la méthode post avec un argument de body manquant
     */
    public function testPostMissingRequiredArg()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->postOne = function () {
            throw new \LibertAPI\Tools\Exceptions\MissingArgumentException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response);

        $this->assertFail($response, 412);
    }

    /**
     * Teste la méthode post avec un argument de body incohérent
     */
    public function testPostBadDomain()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->postOne = function () {
            throw new \DomainException('Status doit être un int');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response);

        $this->assertFail($response, 412);
    }

    /**
     * Teste la méthode post Ok
     */
    public function testPostOk()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->router->getMockController()->pathFor = '';
        $this->repository->getMockController()->postOne = 42;
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response);
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(201);
        $this->array($data)
            ->integer['code']->isIdenticalTo(201)
            ->string['status']->isIdenticalTo('success')
            ->array['data']->isNotEmpty()
        ;
    }

    /**
     * Teste le fallback de la méthode post
     */
    public function testPostFallback()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->postOne = function () {
            throw new \Exception('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response);

        $this->assertError($response);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Teste la méthode put d'un json mal formé
     */
    public function testPutJsonBadFormat()
    {
        // Le framework fait du traitement, un mauvais json est simplement null
        $this->request->getMockController()->getParsedBody = null;
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->put($this->request, $this->response, ['planningId' => 99]);

        $this->assertFail($response, 400);
    }

    /**
     * Teste la méthode put avec un détail non trouvé (id en Bad domaine)
     */
    public function testPutNotFound()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->getOne = function () {
            throw new \DomainException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->put($this->request, $this->response, ['planningId' => 99]);

        $this->boolean($response->isNotFound())->isTrue();
    }

    /**
     * Teste le fallback de la méthode getOne du put
     */
    public function testPutGetOneFallback()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->getOne = function () {
            throw new \LogicException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->put($this->request, $this->response, ['planningId' => 99]);
        $this->assertError($response);
    }

    /**
     * Teste la méthode put avec un argument de body manquant
     */
    public function testPutMissingRequiredArg()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->getOne = $this->entite;

        $this->repository->getMockController()->putOne = function () {
            throw new \LibertAPI\Tools\Exceptions\MissingArgumentException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->put($this->request, $this->response, ['planningId' => 99]);

        $this->assertFail($response, 412);
    }

    /**
     * Teste la méthode put avec un argument de body incohérent
     */
    public function testPutBadDomain()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->getOne = $this->entite;
        $this->repository->getMockController()->putOne = function () {
            throw new \DomainException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->put($this->request, $this->response, ['planningId' => 99]);

        $this->assertFail($response, 412);
    }

    /**
     * Teste le fallback de la méthode putOne du put
     */
    public function testPutPutOneFallback()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->getOne = $this->entite;
        $this->repository->getMockController()->putOne = function () {
            throw new \LogicException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->put($this->request, $this->response, ['planningId' => 99]);
        $this->assertError($response);
    }

    /**
     * Teste la méthode put Ok
     */
    public function testPutOk()
    {
        $this->request->getMockController()->getParsedBody = [];
        $this->repository->getMockController()->getOne = $this->entite;
        $this->repository->getMockController()->putOne = '';
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->put($this->request, $this->response, ['planningId' => 99]);

        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(204);
        $this->array($data)
            ->integer['code']->isIdenticalTo(204)
            ->string['status']->isIdenticalTo('success')
            ->string['data']->isIdenticalTo('')
        ;
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Teste la méthode delete avec un détail non trouvé (id en Bad domaine)
     */
    public function testDeleteNotFound()
    {
        $this->repository->getMockController()->getOne = function () {
            throw new \DomainException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->delete($this->request, $this->response, ['planningId' => 99]);

        $this->boolean($response->isNotFound())->isTrue();
    }

    /**
     * Teste le fallback de la méthode delete
     */
    public function testDeleteFallback()
    {
        $this->repository->getMockController()->getOne = function () {
            throw new \LogicException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->delete($this->request, $this->response, ['planningId' => 99]);
        $this->assertError($response);
    }

    /**
     * Teste la méthode delete Ok
     */
    public function testDeleteOk()
    {
        $this->repository->getMockController()->getOne = $this->entite;
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->delete($this->request, $this->response, ['planningId' => 99]);
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            ->array['data']->isNotEmpty()
        ;
    }
}
