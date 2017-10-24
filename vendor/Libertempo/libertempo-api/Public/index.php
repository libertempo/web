<?php
/**
 * API de Libertempo
 * @since 0.1
 */
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

define('ROOT_PATH', dirname(__DIR__) . '/');
define('ROUTE_PATH', ROOT_PATH . 'Route/');

require_once ROOT_PATH . 'Vendor/autoload.php';
$container = [];
require_once ROOT_PATH . 'Handlers.php';

$app = new \Slim\App($container);

require_once ROOT_PATH . 'Middlewares.php';

$app->get('/hello_world', function(IRequest $request, IResponse $response) {
    return $response->withJson('Hi there !');
});

require_once ROUTE_PATH . 'Plannings.php';
require_once ROUTE_PATH . 'Authentification.php';

/* Jump in ! */
$app->run();
