<?php
/**
 * API de Libertempo
 * @version 0.1
 */
define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
define('CONFIG_PATH', ROOT_PATH . 'cfg/');
define('API_PATH', ROOT_PATH . 'Api/');
define('MIDDLEWARE_PATH', API_PATH . 'Middlewares/');

define('ROUTE_PATH', API_PATH . 'Route/');

/* Virer cette cochonnerie dès que possible */
define('_PHP_CONGES', 1);
require_once ROOT_PATH . 'vendor/autoload.php';
$container = [];
require_once MIDDLEWARE_PATH . 'Handlers.php';

$app = new \Slim\App($container);

/**
 * creation des controllers X
 * // creation des repositories X
 * creation des acces db X
 * creation des dao
 * creation des domain models
 * creation des collections
 */

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

require_once ROUTE_PATH . 'Default.php';

/* Jump in ! */
$app->run();
