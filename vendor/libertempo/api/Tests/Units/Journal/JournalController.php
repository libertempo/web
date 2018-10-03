<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Journal;

use LibertAPI\Utilisateur\UtilisateurEntite;

/**
 * Classe de test du contrôleur de journal
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class JournalController extends \LibertAPI\Tests\Units\Tools\Libraries\AController
{
    /**
     * @var UtilisateurEntite Standardisation d'un rôle employé
     */
    protected $currentEmploye;

    /**
     * {@inheritdoc}
     */
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->currentEmploye = new UtilisateurEntite(['id' => 'user', 'isResp' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function initRepository()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->repository = new \mock\LibertAPI\Journal\JournalRepository();
    }

    /**
     * {@inheritdoc}
     */
    protected function initEntite()
    {
        $this->entite = new \LibertAPI\Journal\JournalEntite([
            'id' => 42,
            'numeroPeriode' => 88,
            'utilisateurActeur' => '4',
            'utilisateurObjet' => '8',
            'etat' => 'cassé',
            'commentaire' => 'c\'est cassé',
            'date' => 'now',
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
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);
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
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);
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
        $this->newTestedInstance($this->repository, $this->router, $this->currentEmploye);

        $response = $this->testedInstance->get($this->request, $this->response, []);
        $this->assertError($response);
    }
}
