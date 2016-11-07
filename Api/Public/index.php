<?php
/**
 * API de Libertempo
 * @version 0.1
 */
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

define('ROOT_PATH', dirname(dirname(__DIR__)) . '/');
define('CONFIG_PATH', ROOT_PATH . 'cfg/');
define('API_PATH', ROOT_PATH . 'Api/');

define('ROUTE_PATH', API_PATH . 'Route/');

/* Virer cette cochonnerie dès que possible */
define('_PHP_CONGES', 1);
require_once ROOT_PATH . 'vendor/autoload.php';
$container = [];
require_once API_PATH . 'Handlers.php';

$app = new \Slim\App($container);

require_once API_PATH . 'Middlewares.php';

$app->get('/hello_world', function(IRequest $request, IResponse $response) {
    $response->withJson('Hi there !');

    return $response;
});

require_once ROUTE_PATH . 'Plannings.php';

/* Jump in ! */
$app->run();
