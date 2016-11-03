<?php

/*
 * Doit être importé après la création de $app. Ne créé rien.
 *
 * La convention de nommage est de mettre les routes au pluriel
 */
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/* Sécurité via authentification */
$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    /**
     * TODO
     */
    if ((new \Api\Middlewares\Authentication($request))->isTokenApiOk()) {
        return $next($request, $response);
    } else {
        return call_user_func(
            $this->unauthorizedHandler,
            $request,
            $response
        );
    }
});

/* Sécurité via droits d'accès sur la ressource */
$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    /**
     * TODO
     *
     * qu'est ce que ça veut dire qu'une ressource est accessible, et où le mettre ? dépend du rôle ?
     */
    if (true) {
        return $next($request, $response);
    } else {
        return call_user_func(
            $this->forbiddenHandler,
            $request,
            $response
        );
    }
});

/* Construction du contrôleur pour le Dependencies Injection Container */
$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    // WIP dépot du contrôleur dans le DIC
});

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
                        $this->callDefaultList,
                        $request,
                        $response,
                        $this,
                        $resourceName
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
