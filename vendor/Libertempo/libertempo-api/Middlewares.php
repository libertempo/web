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

/* Middleware 5 : sécurité via droits d'accès sur la ressource */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    /**
     * TODO
     *
     * qu'est ce que ça veut dire qu'une ressource est accessible, et où le mettre ? dépend du rôle ?
     * On peut désormais s'appuyer sur le DIC : $this['currentUser']
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

/* Middleware 4 : sécurité via identification (+ « ping » last access) */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    $repoUtilisateur = new \App\Components\Utilisateur\Repository(
        new \App\Components\Utilisateur\Dao($this['storageConnector'])
    );
    $identification = new \Middlewares\Identification($request, $repoUtilisateur);
    $reserved = ['Authentification'];
    $ressourcePath = $request->getAttribute('nomRessources');
    if (in_array($ressourcePath, $reserved, true)) {
        return $next($request, $response);
    } elseif ($identification->isTokenOk()) {
        $utilisateur = $identification->getUtilisateur();
        // Ping de last_access
        $repoUtilisateur->updateDateLastAccess($utilisateur);

        $this['currentUser'] = $utilisateur;
        return $next($request, $response);
    } else {
        return call_user_func(
            $this->unauthorizedHandler,
            $request,
            $response
        );
    }
});

/* Middleware 3 : connexion DB */
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

/* Middleware 2 : découverte et mise en forme des noms de ressources */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    $path = trim(trim($request->getUri()->getPath()), '/');
    if (0 === stripos($path, 'api')) {
        $uriUpdated = $request->getUri()->withPath(substr($path, 4));
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

/* Middleware 1 : vérification des headers */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    /* /!\ Headers non versionnés */
    $json = 'application/json';
    if ($request->getHeaderLine('Accept') === $json && $request->getHeaderLine('Content-Type') === $json) {
        return $next($request, $response);
    } else {
        return call_user_func(
            $this->badRequestHandler,
            $request,
            $response
        );
    }
});
