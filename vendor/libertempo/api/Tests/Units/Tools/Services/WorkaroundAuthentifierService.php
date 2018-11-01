<?php declare(strict_types = 1);
namespace LibertAPI\Tests\Units\Tools\Services;

use LibertAPI\Tools\Services;
use LibertAPI\Tools\Exceptions\BadRequestException;

/**
 * Classe de test du service d'authentification de contournement temporaire
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.3
 */
class WorkaroundAuthentifierService extends \Atoum
{
    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);

        $this->mockGenerator->orphanize('__construct');
        $this->repository = new \mock\LibertAPI\Tools\Libraries\ARepository();
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->request = new \mock\Slim\Http\Request();
    }

    public function testIsAuthentificationSucceedBadRequest()
    {
        $this->calling($this->request)->getHeaderLine = 'Lollipop';

        $this->exception(function() {
            $this->newTestedInstance($this->repository);
            $this->testedInstance->isAuthentificationSucceed($this->request);
        })->isInstanceOf(BadRequestException::class);
    }

    public function testIsAuthentificationSucceedFalse()
    {
        $this->calling($this->request)->getHeaderLine = 'Basic QWxhZGRpbjpPcGVuU2VzYW1l';
        $entite = new \LibertAPI\Utilisateur\UtilisateurEntite([
            'id' => 42,
            'password' => md5('Fatboy slim'),
        ]);
        $this->calling($this->repository)->find = $entite;
        $this->newTestedInstance($this->repository);
        $succeed = $this->testedInstance->isAuthentificationSucceed($this->request);

        $this->boolean($succeed)->isFalse();
    }

    public function testIsAuthentificationSucceedTrue()
    {
        $this->calling($this->request)->getHeaderLine = 'Basic QWxhZGRpbjpPcGVuU2VzYW1l';
        $entite = new \LibertAPI\Utilisateur\UtilisateurEntite([
            'id' => 42,
            'password' => md5('none'),
        ]);
        $this->calling($this->repository)->find = $entite;
        $this->newTestedInstance($this->repository);
        $succeed = $this->testedInstance->isAuthentificationSucceed($this->request);

        $this->boolean($succeed)->isTrue();
    }

    /**
    * @var LibertAPI\Tools\Libraries\ARepository Mock d'un repository lambda
    */
    private $repository;

    /**
     * @var \Slim\Http\Request Mock de la requête HTTP
     */
    protected $request;
}
