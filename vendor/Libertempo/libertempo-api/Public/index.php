<?php
/**
 * API de Libertempo
 * @since 0.1
 */
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DS);
define('TOOLS_PATH', ROOT_PATH . 'Tools' . DS);
define('ROUTE_PATH', TOOLS_PATH . 'Route' . DS);

require_once ROOT_PATH . 'Vendor' . DS . 'autoload.php';
$container = [];
require_once TOOLS_PATH . 'Handlers.php';

$app = new \Slim\App($container);

require_once TOOLS_PATH . 'Middlewares.php';

$app->get('/hello_world', function(IRequest $request, IResponse $response) {
    return $response->withJson('Hi there !');
});

require_once ROUTE_PATH . 'Plannings.php';
require_once ROUTE_PATH . 'Authentification.php';
require_once ROUTE_PATH . 'Utilisateurs.php';

/* Jump in ! */
$app->run();
