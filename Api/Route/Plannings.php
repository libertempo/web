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
    $this->group('/{planningId:[0-9]+}', function () use ($resourceName) {
        /* Detail */
        $this->map(
            ['GET', 'DELETE', 'PUT'],
            '',
            function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($resourceName) {
                return call_user_func(
                    $this->callDefaultDetail,
                    $request,
                    $response,
                    $this,
                    $resourceName,
                    $args['planningId']
                );
        })->setName('planning-detail');

        /* Dependances de plannings */
        $this->group('/creneaux', function () {
            $resourceName = 'creneaux';
            /* Detail creneaux */
            $this->map(
            ['GET', 'DELETE', 'PUT'],
            '/{creneauId:[0-9]+}',
            function(ServerRequestInterface $request, ResponseInterface $response, array $args) use ($resourceName) {
                $data = ['planningId' => (int) $args['planningId']];
                return call_user_func(
                    $this->notFoundHandler,
                    $request,
                    $response
                );
            })->setName('creneau-detail');

            /* Collection creneaux */
            $this->map(
                ['GET', 'POST'],
                '',
                function(ServerRequestInterface $request, ResponseInterface $response, array $args) use ($resourceName) {
                    $data = ['planningId' => (int) $args['planningId']];
                    return call_user_func(
                        $this->notFoundHandler,
                        $request,
                        $response
                    );
            })->setName('creneau-liste');

        });
    });

    /* Collection */
    $this->map(
        ['GET', 'POST'],
        '',
        function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($resourceName) {
            return call_user_func(
                $this->callDefaultList,
                $request,
                $response,
                $this,
                $resourceName
            );
    })->setName('planning-liste');
});
