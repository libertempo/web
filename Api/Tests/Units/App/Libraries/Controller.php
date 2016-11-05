<?php
namespace Api\Tests\Units\App\Libraries;

/**
 * Classe de base des tests sur les contrôleurs
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
class Controller extends \Atoum
{
    /**
     * @var \mock\Slim\Http\Request Mock de la requête HTTP
     */
    protected $request;

    /**
     * @var \mock\Slim\\Http\Response Mock de la réponse HTTP
     */
    protected $response;

    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->request = new \mock\Slim\Http\Request();
        $this->response = new \mock\Slim\Http\Response();
    }
}
