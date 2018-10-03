<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Interfaces;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Interface décrivant la consommation de l'ordre REST POST
 */
interface IPostable
{
    /**
     * Execute l'ordre HTTP POST
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     * @param array $routeArguments Arguments de route
     *
     * @return IResponse
     */
    public function post(IRequest $request, IResponse $response, array $routeArguments) : IResponse;
}
