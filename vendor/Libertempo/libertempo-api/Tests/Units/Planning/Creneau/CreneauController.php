<?php
namespace LibertAPI\Tests\Units\Planning\Creneau;

/**
 * Classe de test du contrôleur de créneau de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class CreneauController extends \LibertAPI\Tests\Units\Tools\Libraries\ARestController
{
    protected function initRepository()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->repository = new \mock\LibertAPI\Planning\Creneau\CreneauRepository();
    }

    protected function initEntite()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->entite = new \mock\LibertAPI\Planning\Creneau\CreneauEntite();
        $this->entite->getMockController()->getId = 42;
        $this->entite->getMockController()->getPlanningId = 12;
        $this->entite->getMockController()->getJourId = 12;
        $this->entite->getMockController()->getTypeSemaine = 12;
        $this->entite->getMockController()->getTypePeriode = 12;
        $this->entite->getMockController()->getDebut = 12;
        $this->entite->getMockController()->getFin = 12;
    }

    protected function getOne()
    {
        return $this->testedInstance->get($this->request, $this->response, ['creneauId' => 99, 'planningId' => 45]);
    }

    protected function getList()
    {
        return $this->testedInstance->get($this->request, $this->response, ['planningId' => 45]);
    }

    /*************************************************
     * POST
     *************************************************/

    // post ok

    /**
     * Teste la méthode post d'un json mal formé
     */
    public function testPostJsonBadformat()
    {
        // Le framework fait du traitement, un mauvais json est simplement null
        $this->request->getMockController()->getParsedBody = null;
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response, ['planningId' => 11]);

        $this->assertFail($response, 400);
    }

    /**
     * Teste la méthode post avec un argument de body manquant
     */
    public function testPostMissingArgument()
    {
        $this->request->getMockController()->getParsedBody = [[]];
        $this->repository->getMockController()->postList = function () {
            throw new \LibertAPI\Tools\Exceptions\MissingArgumentException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response, ['planningId' => 11]);

        $this->assertFail($response, 412);
    }

    /**
     * Teste la méthode post avec un argument de body incohérent
     */
    public function testPostBadDomain()
    {
        $this->request->getMockController()->getParsedBody = [[]];
        $this->repository->getMockController()->postList = function () {
            throw new \DomainException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response, ['planningId' => 11]);

        $this->assertFail($response, 412);
    }

    /**
     * Teste le fallback de la méthode post
     */
    public function testPostFallback()
    {
        $this->request->getMockController()->getParsedBody = [[]];
        $this->repository->getMockController()->postList = function () {
            throw new \LogicException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response, ['planningId' => 11]);
        $this->assertError($response);
    }

    /**
     * Teste la méthode post tout ok
     */
    public function testPostOk()
    {
        $this->request->getMockController()->getParsedBody = [[]];
        $this->router->getMockController()->pathFor = '';
        $this->repository->getMockController()->postList = [42, 74, 314];
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->post($this->request, $this->response, ['planningId' => 11]);
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(201);
        $this->array($data)
            ->integer['code']->isIdenticalTo(201)
            ->string['status']->isIdenticalTo('success')
            ->array['data']->isNotEmpty()
        ;
        $this->integer(count($data['data']))->isIdenticalTo(3);
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

        $response = $this->testedInstance->put($this->request, $this->response, ['creneauId' => 99, 'planningId' => 11]);

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

        $response = $this->testedInstance->put($this->request, $this->response, ['creneauId' => 99, 'planningId' => 11]);

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

        $response = $this->testedInstance->put($this->request, $this->response, ['creneauId' => 99, 'planningId' => 11]);
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

        $response = $this->testedInstance->put($this->request, $this->response, ['creneauId' => 99, 'planningId' => 11]);

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

        $response = $this->testedInstance->put($this->request, $this->response, ['creneauId' => 99, 'planningId' => 11]);

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

        $response = $this->testedInstance->put($this->request, $this->response, ['creneauId' => 99, 'planningId' => 11]);
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

        $response = $this->testedInstance->put($this->request, $this->response, ['creneauId' => 99, 'planningId' => 11]);

        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(204);
        $this->array($data)
            ->integer['code']->isIdenticalTo(204)
            ->string['status']->isIdenticalTo('success')
            ->string['data']->isIdenticalTo('')
        ;
    }
}
