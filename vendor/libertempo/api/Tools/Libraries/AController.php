<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Libraries;

use LibertAPI\Tools\Libraries\ARepository;
use LibertAPI\Tools\Libraries\AEntite;
use \Slim\Interfaces\RouterInterface as IRouter;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IRequest;

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

    public function __construct(ARepository $repository, IRouter $router)
    {
        $this->repository = $repository;
        $this->router = $router;
    }

    /**
     * Retourne une réponse de succès normalisée
     *
     * @param IResponse $response Réponse Http
     * @param mixed $messageData Message data d'un json bien formé
     * @param int $code Code Http
     *
     * @return IResponse
     */
    final protected function getResponseSuccess(IResponse $response, $messageData, int $code) : IResponse
    {
        return $this->getResponse($response, $messageData, $code, 'success');
    }

    /**
     * Retourne une réponse d'erreur normalisée
     *
     * @param IResponse $response Réponse Http
     * @param \Exception $e Cas d'échec
     *
     * @return IResponse
     */
    final protected function getResponseError(IResponse $response, \Exception $e) : IResponse
    {
        return $this->getResponse($response, $e->getMessage(), 500, 'error');
    }

    /**
     * Retourne une réponse 400 normalisée
     *
     * @param IResponse $response Réponse Http
     * @param string $message Message d'erreur
     *
     * @return IResponse
     */
    final protected function getResponseBadRequest(IResponse $response, string $message) : IResponse
    {
        return $this->getResponseFail($response, $message, 400);
    }

    /**
     * Retourne une réponse normalisée d'argument manquant
     *
     * @param IResponse $response Réponse Http
     *
     * @return IResponse
     */
    final protected function getResponseMissingArgument(IResponse $response) : IResponse
    {
        return $this->getResponseFail($response, 'Missing required argument', 412);
    }

    /**
     * Retourne une réponse normalisée d'argument en bad domaine
     *
     * @param IResponse $response Réponse Http
     * @param \Exception $e Tableau des champs en erreur jsonEncodé
     *
     * @return IResponse
     */
    final protected function getResponseBadDomainArgument(IResponse $response, \Exception $e) : IResponse
    {
        return $this->getResponseFail($response, json_decode($e->getMessage(), true), 412);
    }

    /**
     * Retourne une réponse normalisée d'élément non trouvé
     *
     * @param IResponse $response Réponse Http
     * @param string $messageData Message data d'un json bien formé
     *
     * @return IResponse
     */
    final protected function getResponseNotFound(IResponse $response, string $messageData) : IResponse
    {
        return $this->getResponseFail($response, $messageData, 404);
    }

    /**
     * Retourne une réponse normalisée d'élément sans contenu
     *
     * @param IResponse $response Réponse Http
     *
     * @return IResponse
     */
    final protected function getResponseNoContent(IResponse $response) : IResponse
    {
        return $this->getResponseSuccess($response, 'No Content', 204);
    }

    /**
     * Retourne une réponse d'échec normalisée
     *
     * @param IResponse $response Réponse Http
     * @param mixed $messageData Message data d'un json bien formé
     * @param int $code Code Http
     *
     * @return IResponse
     */
    private function getResponseFail(IResponse $response, $messageData, int $code) : IResponse
    {
        return $this->getResponse($response, $messageData, $code, 'fail');
    }

    /**
     * Retourne une réponse normalisée
     *
     * @param IResponse $response Réponse Http
     * @param mixed $messageData Message data d'un json bien formé
     * @param int $code Code Http
     * @param string $status Statut textuel correspondant à la classe du code (fail | error | success)
     *
     * @return IResponse
     */
    private function getResponse(IResponse $response, $messageData, int $code, string $status) : IResponse
    {
        $response = $response->withStatus($code);
        $data = [
            'code' => $code,
            'status' => $status,
            'message' => $response->getReasonPhrase(),
            'data' => $messageData,
        ];

        return $response->withJson($data);
    }
}
