<?php
namespace LibertAPI\Tests\Units\Authentification;

/**
 * Classe de test du contrôleur de planning
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class AuthentificationController extends \LibertAPI\Tests\Units\Tools\Libraries\AController
{
    protected function initRepository()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->repository = new \mock\LibertAPI\Utilisateur\UtilisateurRepository();
    }

    protected function initEntite()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->entite = new \mock\LibertAPI\Utilisateur\UtilisateurEntite();
        $this->entite->getMockController()->getId = 42;
        $this->entite->getMockController()->getToken = 12;
        $this->entite->getMockController()->getLogin = 12;
        $this->entite->getMockController()->getNom = 12;
        $this->entite->getMockController()->getDateInscription = 12;
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
        $this->newTestedInstance($this->repository, $this->router);

        $response = $this->testedInstance->get($this->request, $this->response);

        $this->assertFail($response, 400);
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
        $this->newTestedInstance($this->repository, $this->router);

        $response = $this->testedInstance->get(
            $this->request,
            $this->response
        );

        $this->assertFail($response, 404);
    }

    /**
     * Teste la méthode get d'une authentification réussie
     */
    public function testGetFound()
    {
        $token = 'abcde';
        $this->entite->getMockController()->getToken = $token;
        $this->repository->getMockController()->find = $this->entite;
        $this->repository->getMockController()->regenerateToken = $this->entite;
        $this->request->getMockController()->getHeaderLine = 'Basic QWxhZGRpbjpPcGVuU2VzYW1l';
        $this->newTestedInstance($this->repository, $this->router);

        $response = $this->testedInstance->get($this->request, $this->response);
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            ->string['data']->isIdenticalTo($token)
        ;
    }

    protected function getOne()
    {
        return $this->testedInstance->get($this->request, $this->response, ['utilisateurId' => 99,]);
    }

    protected function getList()
    {
        return $this->testedInstance->get($this->request, $this->response, []);
    }
}
