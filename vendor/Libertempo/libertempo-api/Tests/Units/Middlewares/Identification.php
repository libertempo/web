<?php
namespace Tests\Units\Middlewares;

use Middlewares\Identification as _Identification;

/**
 * Test de l'identification d'un utilisateur
 *
 * @since 0.1
 */
final class Identification extends \Atoum
{
    /**
     * @var \mock\Slim\Http\Request Mock de la requête HTTP
     */
    private $request;

    /**
     * @var \mock\App\Components\Utilisateur\Repository
     */
    private $repository;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->request = new \mock\Slim\Http\Request();
        $this->calling($this->request)->getHeaderLine = 'token';
        $this->mockGenerator->orphanize('__construct');
        $this->repository = new \mock\App\Components\Utilisateur\Repository();
    }

    /**
     * Teste si le token api est bon
     */
    public function testIsTokenOkOk()
    {
        $this->calling($this->repository)->find = function () {
            return new \App\Components\Utilisateur\Model([]);
        };
        $auth = new _Identification($this->request, $this->repository);

        $this->boolean($auth->isTokenOk())->isTrue();
    }

    /**
     * Teste si le token api est ko
     */
    public function testIsTokenOkKo()
    {
        $this->calling($this->repository)->find = null;
        $auth = new _Identification($this->request, $this->repository);

        $this->boolean($auth->isTokenOk())->isFalse();
    }
}
