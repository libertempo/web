<?php
/*
 * Doit être importé après la création de $app.
 *
 * /!\ Les Middlewares sont executés en mode PILE : le premier de la liste est lancé en dernier
 */
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/* Middleware 5 : construction du contrôleur pour le Dependencies Injection Container */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    $ressourcePath = str_replace('|', '\\', $request->getAttribute('nomRessources'));
    $controllerClass = '\Api\App\\' . $ressourcePath . '\Controller';
    $daoClass = '\Api\App\\' . $ressourcePath . '\Dao';
    $repoClass = '\Api\App\\' . $ressourcePath . '\Repository';

    $this[$controllerClass] = new $controllerClass(
        new $repoClass(
            new $daoClass($this['storageConnector'])
        )
    );

    return $next($request, $response);
});

/* Middleware 4 : découverte et mise en forme des noms de ressources */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    $path = trim(trim($request->getUri()->getPath()), '/');
    $paths = explode('/', $path);
    $ressources = [];
    foreach ($paths as $value) {
        if (!is_numeric($value)) {
            $ressources[] = \Api\App\Helpers\Formatter::getSingularTerm(
                \Api\App\Helpers\Formatter::getStudlyCapsFromSnake($value)
            );
        }
    }
    $request = $request->withAttribute('nomRessources', implode('|', $ressources));

    return $next($request, $response);
});

/* Middleware 3 : connexion DB */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
    try {
        require_once CONFIG_PATH . 'dbconnect.php';
        $this['storageConnector'] = new \PDO(
        'mysql:host=localhost;dbname=' . $mysql_database,
        $mysql_user,
        $mysql_pass
    );
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

/* Middleware 1 : sécurité via authentification */
$app->add(function (IRequest $request, IResponse $response, callable $next) {
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
