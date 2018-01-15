<?php
/*
 * Doit être importé après la création de $app.
 *
 * /!\ Les Middlewares sont executés en mode PILE : le premier de la liste est lancé en dernier
 */
use LibertAPI\Tools\Libraries\AControllerFactory;
use \LibertAPI\Tools\Helpers\Formatter;
use \LibertAPI\Utilisateur;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use Doctrine\DBAL;

/* Middleware 5 : construction du contrôleur pour le Dependencies Injection Container */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    $reserved = ['HelloWorld'];
    $ressourcePath = str_replace('|', '\\', $request->getAttribute('nomRessources'));
    if (!in_array($ressourcePath, $reserved, true)) {
        try {
            if ('Authentification' === $ressourcePath) {
                $controller = AControllerFactory::createControllerAuthentification(
                    $ressourcePath,
                    $this['storageConnector'],
                    $this->router
                );
            } else {
                $controller = AControllerFactory::createControllerWithUser(
                    $ressourcePath,
                    $this['storageConnector'],
                    $this->router,
                    $this['currentUser']
                );
            }
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

/* Middleware 4 : sécurité via identification (+ « ping » last access) */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    $repoUtilisateur = new Utilisateur\UtilisateurRepository(
        new Utilisateur\UtilisateurDao($this['storageConnector'])
    );
    $identification = new \LibertAPI\Tools\Middlewares\Identification($request, $repoUtilisateur);
    $reserved = ['Authentification', 'HelloWorld'];
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
        $config = new DBAL\Configuration();
        $connexion = DBAL\DriverManager::getConnection(
            [
                'driver' => 'pdo_mysql',
                'host' => $configuration->db->serveur,
                'dbname' => $configuration->db->base,
                'user' => $configuration->db->utilisateur,
                'password' => $configuration->db->mot_de_passe,
            ],
            $config);
        $this['storageConnector'] = $connexion;

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
            $ressources[] = Formatter::getSingularTerm(
                Formatter::getStudlyCapsFromSnake($value)
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
