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

    public function beforeTestMethod($method)
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
        $auth = new _Identification($this->request);

        $this->boolean($auth->isTokenApiOk())->isTrue();
    }
}
