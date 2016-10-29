<?php
/**
 * API de Libertempo
 * @version 0.1
 */
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH. '/vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**************************
 * Handlers par défaut
 **************************/
$container['unauthorizedHandler'] = function () {
    return function (ServerRequestInterface $request, ResponseInterface $response) {
        $data = [
            'code' => 401,
            'status' => 'error',
            'message' => 'Unauthorized',
            'data' => 'Bad API Key',
        ];

        return $response->withJson($data, 401);
    };
};

$container['forbiddenHandler'] = function () {
    return function (ServerRequestInterface $request, ResponseInterface $response) {
        $data = [
            'code' => 403,
            'status' => 'error',
            'message' => 'Forbidden',
            'data' => 'User has not access to « ' . $request->getUri()->getPath() . ' » resource',
        ];

        return $response->withJson($data, 403);
    };
};

$container['notFoundHandler'] = function () {
    return function (ServerRequestInterface $request, ResponseInterface $response) {
        return $response->withJson([
            'code' => 404,
            'status' => 'error',
            'message' => 'Not Found',
            'data' => '« ' . $request->getUri()->getPath() . ' » is not a valid resource',
        ], 404);
    };
};

$container['notAllowedHandler'] = function () {
    return function (ServerRequestInterface $request, ResponseInterface $response, array $methods) {
        $methodString = implode(', ', $methods);
        $data = [
            'code' => 405,
            'status' => 'error',
            'message' => 'Method Not Allowed',
            'data' => 'Method on « ' . $request->getUri()->getPath() . ' » must be one of : ' . $methodString,
        ];

        return $response
            ->withHeader('Allow', $methodString)
            ->withJson($data, 405);
    };
};

$container['errorHandler'] = function () {
    return function (ServerRequestInterface $request, ResponseInterface $response, \Exception $exception) {
        return $response->withJson([
            'code' => 500,
            'status' => 'fail',
            'message' => 'Internal Server Error',
            'data' => $exception->getMessage(),
        ], 500);
    };
};

$container['createStack'] = function () {
    return function (
        ServerRequestInterface $request,
        ResponseInterface $response,
        \Interop\Container\ContainerInterface $dic,
        $resourceName
    ) {
        $class = \Api\App\Helpers\Formatter::getSingularTerm(
            \Api\App\Helpers\Formatter::getStudlyCapsFromSnake($resourceName)
        );
        $controllerClass = '\Api\App\\' . $class . '\Controller';
        $daoClass = '\Api\App\\' . $class . '\Dao';
        $repoClass = '\Api\App\\' . $class . '\Repository';
        $repoUtilisateurClass = '\Api\App\Utilisateur\Repository';
        $daoUtilisateurClass = '\Api\App\Utilisateur\Dao';
        /* Ressource n'existe pas => 404 */
        if (!class_exists($controllerClass, true)
            || !class_exists($repoClass, true)
            || !class_exists($repoUtilisateurClass, true)
            || !class_exists($daoClass, true)
            || !class_exists($daoUtilisateurClass, true)
        ) {
            return call_user_func(
                $dic->notFoundHandler,
                $request,
                $response
            );
        }
        try {
            // Connexion stockage
            $storageConnector = '';

            return new $controllerClass(
                $request,
                $response,
                new $repoClass(new $daoClass($storageConnector)),
                new $repoUtilisateurClass(new $daoUtilisateurClass($storageConnector))
            );
        } catch (\DomainException $e) {
            return call_user_func(
                $dic->unauthorizedHandler,
                $request,
                $response
            );
        } catch (\LogicException $e) {
            return call_user_func(
                $dic->forbiddenHandler,
                $request,
                $response
            );
        /* Fallback */
        } catch (\Exception $e) {
            return call_user_func(
                $dic->notFoundHandler,
                $request,
                $response
            );
        }
    };
};

$app = new \Slim\App($container);

/**
 * creation des controllers X
 * // creation des repositories
 * creation des acces db
 * creation des dao
 * creation des domain models
 * creation des collections
 */

 /**************************
  * Routage
  **************************/

/**
* Routage de la découverte des urls
*/
$app->get('/hello_world', function($request, $response, $args) {
    $headers = $request->getHeaders();
    /* Check api key and error access : 401 */

    $response->withJson('How about implementing planningsIdGet as a GET method ?');

    return $response;
});

/**
 * Routage général des options
 */
/*
$app->options('/{ressource:[a-z_]+}',
    function(ServerRequestInterface $request, ResponseInterface $response, $args) {
        // snake to StudlyCaps
        // drop the plural
        $class = '\Api\App\\' . ucfirst($args['ressource']) . '\Controller';
        /* Ressource n'existe pas => 404 *
        if (!class_exists($class, true)) {
            return call_user_func(
                $this->notFoundHandler,
                $request,
                $response
            );
        }
        $method = $request->getMethod();
        $controller = new $class($request, $response);
        /* Methode non applicable à la ressource => 405 *
        if (!is_callable([$controller, $method])
            || !in_array($method, $controller->getAvailablesMethods(), true)
        ) {
            return call_user_func(
                $this->notAllowedHandler,
                $request,
                $response,
                array_map('strtoupper', $controller->getAvailablesMethods())
            );
        }

        return call_user_func([$controller, $method]);
    }
);
*/

/**
 * Routage général des détails
 */
$app->map(['GET', 'PUT', 'DELETE'], '/{ressource:[a-z_]+}/{ressourceId:[0-9]+}', function(ServerRequestInterface $request, ResponseInterface $response, $args) {
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
    '/{ressource:[a-z_]+}',
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


/* Jump in ! */
$app->run();
