<?php
namespace LibertAPI\Tools\Interfaces;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Interface décrivant la consommation de l'ordre REST GET
 */
interface IGetable
{
    /**
     * Execute l'ordre HTTP GET
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     * @param array $routeArguments Arguments de route
     *
     * @return IResponse
     */
    public function get(IRequest $request, IResponse $response, array $routeArguments);
}
