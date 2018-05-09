<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Interfaces;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Interface décrivant la consommation de l'ordre REST PUT
 */
interface IPutable
{
    /**
     * Execute l'ordre HTTP PUT
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     * @param array $routeArguments Arguments de route
     *
     * @return IResponse
     */
    public function put(IRequest $request, IResponse $response, array $routeArguments) : IResponse;
}
