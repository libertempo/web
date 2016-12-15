<?php
namespace Api\Tests\Units\Middlewares;

use Api\Middlewares\Authentication as _Authentication;

/**
 * Test de l'authentification
 *
 * @since 0.1
 */
class Authentication extends \Atoum
{
    /**
     * @var \mock\Slim\Http\Request Mock de la requête HTTP
     */
    private $request;

    public function beforeTestMethod()
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->request = new \mock\Slim\Http\Request();
    }

    /**
     * Teste si le token api est bon
     */
    public function testIsTokenApiOkOk()
    {
        $auth = new _Authentication($this->request);

        $this->boolean($auth->isTokenApiOk())->isTrue();
    }
}
