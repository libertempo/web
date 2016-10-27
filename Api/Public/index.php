<?php
/**
 * API de Libertempo
 * @version 0.1
 */
define('ROOT_PATH', dirname(dirname(__DIR__)));
require_once ROOT_PATH. '/vendor/autoload.php';

use Api\App\Helpers\Formatter;
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

        return $response
            ->withJson($data, 401);
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

        return $response
            ->withJson($data, 403);
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

$app = new \Slim\App($container);

/**
 * creation des controllers
 * // creation des repositories
 * creation des acces db
 * creation des dao
 * creation des domain models
 * creation des collections
 */
 /**************************
  * Connexion stockage
  **************************/
$storageConnector = '';

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
    $class = Formatter::getSingularTerm(Formatter::getStudlyCapsFromSnake($args['ressource']));
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
    function(ServerRequestInterface $request, ResponseInterface $response, $args) use ($storageConnector) {
        $allGetVars = $this->request->getQueryParams();
        var_dump($allGetVars);
        exit();
        $class = Formatter::getSingularTerm(Formatter::getStudlyCapsFromSnake($args['ressource']));
        $controllerClass = '\Api\App\\' . $class . '\Controller';
        $repoClass = '\Api\App\\' . $class . '\Repository';
        /* Ressource n'existe pas => 404 */
        if (!class_exists($controllerClass, true)
            || !class_exists($repoClass, true)
        ) {
            return call_user_func(
                $this->notFoundHandler,
                $request,
                $response
            );
        }
        $method = $request->getMethod();
        try {
            $repository = new $repoClass($storageConnector);
            $controller = new $class($request, $response, $repository);
            $availablesMethods = array_map('strtoupper', $controller->getAvailablesMethods());
            /* Méthode non applicable à la ressource => 405 */
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
            return call_user_func([$controller, $method]);
        /* La méthode par exception est une mauvaise piste, car il faut la répeter partout. On peut trouver mieux que ça */
        } catch (\DomainException $e) {
            return call_user_func(
                $this->unauthorizedHandler,
                $request,
                $response
            );
        } catch (\LogicException $e) {
            return call_user_func(
                $this->forbiddenHandler,
                $request,
                $response
            );
        } catch (Exception $e) {
            return call_user_func(
                $this->notFoundHandler,
                $request,
                $response
            );
        }
    }
);


/* Jump in ! */
$app->run();
