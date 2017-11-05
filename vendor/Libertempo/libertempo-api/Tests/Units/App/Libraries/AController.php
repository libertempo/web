<?php
namespace Tests\Units\App\Libraries;

use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Classe de base des tests sur les contrôleurs
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
abstract class AController extends \Atoum
{
    /**
     * @var \mock\Slim\Http\Request Mock de la requête HTTP
     */
    protected $request;

    /**
     * @var \mock\Slim\Http\Response Mock de la réponse HTTP
     */
    protected $response;

    /**
     * @var \mock\Slim\Slim\Router Mock du routeur
     */
    protected $router;

    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->request = new \mock\Slim\Http\Request();
        $this->response = new \mock\Slim\Http\Response();
        $this->router = new \mock\Slim\Router();
    }

    /**
     * Retourne le json décodé
     *
     * @param string $json
     *
     * @return array | mixed si le json est mal formé
     */
    protected function getJsonDecoded($json)
    {
        return json_decode((string) $json, true);
    }

    /**
     * Lance un pool d'assertion d'erreur
     *
     * @param IResponse $response Réponse Http
     * @param int $code Code d'erreur Http attendu
     */
    protected function assertError(IResponse $response, $code)
    {
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo($code);
        $this->array($data)
            ->integer['code']->isIdenticalTo($code)
            ->string['status']->isIdenticalTo('error')
            ->string['message']->isNotEqualTo('')
            ->array['data']->isNotEmpty()
        ;
    }
}
