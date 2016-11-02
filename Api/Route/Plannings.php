<?php

/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au pluriel
 */
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app->group('/plannings', function() {
    $resourceName = 'plannings';
    /* Detail */
    $this->map(
        ['GET', 'DELETE', 'PATCH', 'PUT'],
        '/{id:[0-9]+}',
        function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($resourceName) {
            return call_user_func(
                $this->callDetail,
                $request,
                $response,
                $this,
                $resourceName,
                $args['id']
            );
    })->setName('planning-detail');

    /* Collection */
    $this->map(
        ['GET', 'POST'],
        '',
        function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($resourceName) {
            return call_user_func(
                $this->callList,
                $request,
                $response,
                $this,
                $resourceName
            );
    })->setName('planning-liste');
});
