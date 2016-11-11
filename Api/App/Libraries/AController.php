<?php
namespace Api\App\Libraries;

use \Api\App\Libraries\ARepository;
use \Slim\Interfaces\RouterInterface as IRouter;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur principal
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté par personne
 * Ne devrait contacter personne
 */
abstract class AController
{
    /**
     * @var ARepository Repository de la ressource
     */
    protected $repository;

    /**
     * @var IRouter Routeur de l'application
     */
    protected $router;

    public function __construct(ARepository $repository, IRouter $router) {
        $this->repository = $repository;
        $this->router = $router;
    }

    /**
     * Retourne une réponse 400 normalisée
     *
     * @param IResponse $response Réponse Http
     *
     * @return IResponse
     */
    protected function getResponseBadRequest(IResponse $response)
    {
        $code = 400;
        $data = [
            'code' => $code,
            'status' => 'error',
            'message' => 'Bad Request',
            'data' => 'Body request is not a json',
        ];

        return $response->withJson($data, $code);
    }

    /**
     * Retourne une réponse normalisée d'argument manquant
     *
     * @param IResponse $response Réponse Http
     *
     * @return IResponse
     */
    protected function getResponseMissingArgument(IResponse $response)
    {
        $code = 412;
        $data = [
            'code' => $code,
            'status' => 'error',
            'message' => 'Precondition Failed',
            'data' => 'Missing required argument',
        ];

        return $response->withJson($data, $code);
    }

    /**
     * Retourne une réponse normalisée d'argument en bad domaine
     *
     * @param IResponse $response Réponse Http
     *
     * @return IResponse
     */
    protected function getResponseBadDomainArgument(IResponse $response, \Exception $e)
    {
        $code = 412;
        $data = [
            'code' => $code,
            'status' => 'error',
            'message' => 'Precondition Failed',
            'data' => json_decode($e->getMessage(), true),
        ];

        return $response->withJson($data, $code);
    }
}
