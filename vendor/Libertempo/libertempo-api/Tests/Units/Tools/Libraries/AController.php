<?php
namespace LibertAPI\Tests\Units\Tools\Libraries;

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
     * @var \Slim\Http\Request Mock de la requête HTTP
     */
    protected $request;

    /**
     * @var \Slim\Http\Response Mock de la réponse HTTP
     */
    protected $response;

    /**
     * @var \Slim\Router Mock du routeur
     */
    protected $router;

    /**
     * @var \App\Libraries\ARepository Mock du repository associé
     */
    protected $repository;

    /**
     * @var \App\Libraries\AEntite Mock de l'entité associée
     */
    protected $entite;

    /**
     * Init des tests
     */
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->mockGenerator->orphanize('__construct');
        $this->mockGenerator->shuntParentClassCalls();
        $this->request = new \mock\Slim\Http\Request();
        $this->response = new \mock\Slim\Http\Response();
        $this->router = new \mock\Slim\Router();
        $this->initRepository();
        $this->initEntite();
    }

    /**
     * Initialise un repo bien formé au sens du contrôleur testé
     */
    abstract protected function initRepository();

    /**
     * Initialise une entité bien formée au sens du contrôleur testé
     */
    abstract protected function initEntite();

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
     * Lance un pool d'assertion d'échec
     *
     * @param IResponse $response Réponse Http
     * @param int $code Code d'erreur Http attendu
     */
    protected function assertFail(IResponse $response, $code)
    {
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo($code);
        $this->array($data)
            ->integer['code']->isIdenticalTo($code)
            ->string['status']->isIdenticalTo('fail')
            ->array['data']->isNotEmpty()
        ;
    }

    /**
     * Lance un pool d'assertion d'erreur
     *
     * @param IResponse $response Réponse Http
     */
    protected function assertError(IResponse $response)
    {
        $data = $this->getJsonDecoded($response->getBody());
        $code = 500;

        $this->integer($response->getStatusCode())->isIdenticalTo($code);
        $this->array($data)
            ->integer['code']->isIdenticalTo($code)
            ->string['status']->isIdenticalTo('error')
            ->array['data']->isNotEmpty()
        ;
    }

    /**
     * Lance un pool d'assertion vide
     *
     * @param IResponse $response Réponse Http
     * @param int $code Code Http attendu
     */
    protected function assertSuccessEmpty(IResponse $response)
    {
        $data = $this->getJsonDecoded($response->getBody());

        $this->integer($response->getStatusCode())->isIdenticalTo(204);
        $this->array($data)
            ->integer['code']->isIdenticalTo(204)
            ->string['status']->isIdenticalTo('success')
            ->string['message']->isEqualTo('No Content')
        ;
    }
}
