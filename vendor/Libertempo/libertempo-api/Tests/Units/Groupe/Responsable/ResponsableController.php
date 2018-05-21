<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Groupe\Responsable;

use LibertAPI\Utilisateur\UtilisateurEntite;

/**
 * Classe de test du contrôleur de reponsable de groupe
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 */
final class ResponsableController extends \LibertAPI\Tests\Units\Tools\Libraries\AController
{
    /**
     * @var UtilisateurEntite Standardisation d'un rôle admin
     */
    protected $currentAdmin;

    /**
     * {@inheritdoc}
     */
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->currentAdmin = new UtilisateurEntite(['id' => 'user', 'isAdmin' => true]);
    }

    /**
     * {@inheritdoc}
     */
    protected function initRepository()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->repository = new \mock\LibertAPI\Groupe\Responsable\ResponsableRepository();
    }

    /**
     * {@inheritdoc}
     */
    protected function initEntite()
    {
        $this->entite = new \LibertAPI\Utilisateur\UtilisateurEntite([
            'id' => 816,
            'login' => 'Spider-man',
            'nom' => 'Parker',
            'prenom' => 'Peter',
            'isResp' => 'N',
            'isAdmin' => 'N',
            'isHr' => 'N',
            'isActif' => 'Y',
            'seeAll' => 'N',
            'password' => 'MJ',
            'quotite' => '10',
            'email' => 'p.parker@dailybugle.com',
            'numeroExercice' => 3,
            'planningId' => 666,
            'heureSolde' => 1,
            'dateInscription' => '1-08-1962',
            'token' => '',
            'dateLastAccess' => '1-08-2006',
        ]);
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Teste la méthode get d'une liste trouvée
     */
    public function testGetFound()
    {
        $this->calling($this->request)->getQueryParams = [];
        $this->calling($this->repository)->getList = [$this->entite,];
        $this->newTestedInstance($this->repository, $this->router, $this->currentAdmin);
        $response = $this->testedInstance->get($this->request, $this->response, []);
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(200);
        $this->array($data)
            ->integer['code']->isIdenticalTo(200)
            ->string['status']->isIdenticalTo('success')
            ->string['message']->isIdenticalTo('OK')
            //->array['data']->hasSize(1) // TODO: l'asserter atoum en sucre syntaxique est buggé, faire un ticket
        ;
        $this->array($data['data'][0])->hasKey('id');
    }

    /**
     * Teste la méthode get d'une liste non trouvée
     */
    public function testGetNotFound()
    {
        $this->calling($this->request)->getQueryParams = [];
        $this->calling($this->repository)->getList = function () {
            throw new \UnexpectedValueException('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentAdmin);
        $response = $this->testedInstance->get($this->request, $this->response, []);

        $this->assertSuccessEmpty($response);
    }

    /**
     * Teste le fallback de la méthode get d'une liste
     */
    public function testGetFallback()
    {
        $this->calling($this->request)->getQueryParams = [];
        $this->calling($this->repository)->getList = function () {
            throw new \Exception('');
        };
        $this->newTestedInstance($this->repository, $this->router, $this->currentAdmin);

        $response = $this->testedInstance->get($this->request, $this->response, []);
        $this->assertError($response);
    }
}
