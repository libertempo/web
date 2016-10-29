<?php

/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au pluriel
 */

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Routage général des détails
 */
$app->map(['GET', 'PUT', 'DELETE'], '/{ressource:[a-z_]+}s/{ressourceId:[0-9]+}', function(ServerRequestInterface $request, ResponseInterface $response, $args) {
    $class = \Api\App\Helpers\Formatter::getSingularTerm(
        \Api\App\Helpers\Formatter::getStudlyCapsFromSnake($resourceName)
    );
    /* Ressource n'existe pas => 404 */
    if (!class_exists($class, true)) {
        return call_user_func(
            $this->notFoundHandler,
            $request,
            $response
        );
    }
    $method = $request->getMethod();
    /* Methode non applicable à la ressource => 405 */
    $controller = new $class($request, $response);
    $availablesMethods = array_map('strtoupper', $controller->getAvailablesMethods());
    if (!is_callable([$controller, $method])
        || !in_array($method, $availablesMethods, true)
    ) {
        return call_user_func(
            $this->notAllowedHandler,
            $request,
            $response,
            $availablesMethods
        );
    }

    return call_user_func([$controller, $method], $args['ressourceId']);
});

/**
 * Routage général des collections
 */
$app->map(
    ['GET', 'POST'],
    '/{ressource:[a-z_]+}s',
    function(ServerRequestInterface $request, ResponseInterface $response, $args) {
        $tryCreateController = call_user_func(
            $this->createStack,
            $request,
            $response,
            $this,
            $args['ressource']
        );

        if ($tryCreateController instanceof ResponseInterface) {
            return $tryCreateController;
        }

        $availablesMethods = array_map('strtoupper', $tryCreateController->getAvailablesMethods());
        $method = $request->getMethod();
        /* Méthode non applicable à la ressource => 405 */
        if (!is_callable([$tryCreateController, $method])
            || !in_array($method, $availablesMethods, true)
        ) {
            return call_user_func(
                $this->notAllowedHandler,
                $request,
                $response,
                $availablesMethods
            );
        }

        return call_user_func([$tryCreateController, $method]);
    }
);
