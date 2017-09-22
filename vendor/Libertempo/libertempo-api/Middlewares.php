<?php
/*
 * Doit être importé après la création de $app.
 *
 * /!\ Les Middlewares sont executés en mode PILE : le premier de la liste est lancé en dernier
 */
use App\Libraries\AControllerFactory;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/* Middleware 6 : construction du contrôleur pour le Dependencies Injection Container */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    $reserved = ['HelloWorld'];
    $ressourcePath = str_replace('|', '\\', $request->getAttribute('nomRessources'));
    if (!in_array($ressourcePath, $reserved, true)) {
        try {
            $controller = AControllerFactory::createController(
                $ressourcePath,
                $this['storageConnector'],
                $this->router
            );
            $this[AControllerFactory::getControllerClassname($ressourcePath)] = $controller;

        } catch (\DomainException $e) {
            return call_user_func(
                $this->notFoundHandler,
                $request,
                $response
            );
        }
    }

    return $next($request, $response);
});

/* Middleware 5 : découverte et mise en forme des noms de ressources */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    $path = trim(trim($request->getUri()->getPath()), '/');
    $api = 'api/';
    $position = mb_stripos($path, $api);
    if (false !== $position) {
        $uriUpdated = $request->getUri()->withPath('/' . substr($path, $position + strlen($api)));
        $request = $request->withUri($uriUpdated);
        $path = trim(trim($request->getUri()->getPath()), '/');
    }
    $paths = explode('/', $path);
    $ressources = [];
    foreach ($paths as $value) {
        if (!is_numeric($value)) {
            $ressources[] = \App\Helpers\Formatter::getSingularTerm(
                \App\Helpers\Formatter::getStudlyCapsFromSnake($value)
            );
        }
    }
    $request = $request->withAttribute('nomRessources', implode('|', $ressources));

    return $next($request, $response);
});

/* Middleware 4 : connexion DB */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    try {
        $configuration = json_decode(file_get_contents(ROOT_PATH . 'configuration.json'));
        $dbh = new \PDO(
            'mysql:host=' . $configuration->db->serveur . ';dbname=' . $configuration->db->base,
            $configuration->db->utilisateur,
            $configuration->db->mot_de_passe
        );
        // MYSQL_ATTR_FOUND_ROWS
        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this['storageConnector'] = $dbh;

        return $next($request, $response);
    /* Fallback */
    } catch (\Exception $e) {
        return call_user_func(
            $this->errorHandler,
            $request,
            $response,
            $e
        );
    }
});

/* Middleware 3 : vérification des headers (peut-être 1 ?) */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    /* /!\ Headers non versionnés */
    $json = 'application/json';
    if (($request->hasHeader('Accept') && $request->getHeaderLine('Accept') === $json)
        && ($request->hasHeader('Content-Type') && $request->getHeaderLine('Content-Type') === $json)
    ) {
        return $next($request, $response);
    } else {
        return call_user_func(
            $this->badRequestHandler,
            $request,
            $response
        );
    }
});

/* Middleware 2 : sécurité via droits d'accès sur la ressource */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
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

/* Middleware 1 : sécurité via identification */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    /**
    * TODO
    */
    $reserved = ['Authentification'];
    $ressourcePath = $request->getAttribute('nomRessources');
    if (in_array($ressourcePath, $reserved, true)) {
        return $next($request, $response);
    } elseif ((new \Middlewares\Identification($request))->isTokenApiOk()) {
        return $next($request, $response);
    } else {
        return call_user_func(
            $this->unauthorizedHandler,
            $request,
            $response
        );
    }
});
