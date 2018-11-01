<?php declare(strict_types = 1);
/**
 * API de Libertempo
 * @since 0.1
 */
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use LibertAPI\Tools\Middlewares;
use DI\ContainerBuilder;

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DS);
define('TOOLS_PATH', ROOT_PATH . 'Tools' . DS);
define('ROUTE_PATH', TOOLS_PATH . 'Route' . DS);

require_once ROOT_PATH . 'Vendor' . DS . 'autoload.php';

$containerBuilder = new ContainerBuilder;
$containerBuilder->addDefinitions(ROOT_PATH . 'di-config.php');
$app = new \Slim\App($containerBuilder->build());

/*
 * Commençons simple et posons le paramétrage des assert ici.
 * Précision : une partie est faite côté php.ini (nouvelle norme)
 */
if ('development' == $app->getContainer()->get('environment')->get('stage')) {
    ini_set('assert.exception', '1');
} else {
    assert_options(ASSERT_ACTIVE, 0);
    ini_set('assert.exception', '0');
}

/*
 * /!\ Les Middlewares sont executés en mode PILE : le premier de la liste est lancé en dernier
 */
$app->add(new Middlewares\AccessChecker($app));
$app->add(new Middlewares\Identificator($app));
$app->add(new Middlewares\DBConnector($app));
$app->add(new Middlewares\ConfigurationFileChecker($app));
$app->add(new Middlewares\ResourceFormatter($app));
$app->add(new Middlewares\HeadersChecker($app));

$app->get('/hello_world', function(IRequest $request, IResponse $response) {
    return $response->withJson('Hi there !');
});

require_once ROUTE_PATH . 'Absence.php';
require_once ROUTE_PATH . 'Authentification.php';
require_once ROUTE_PATH . 'Groupe.php';
require_once ROUTE_PATH . 'Journal.php';
require_once ROUTE_PATH . 'JourFerie.php';
require_once ROUTE_PATH . 'Planning.php';
require_once ROUTE_PATH . 'Utilisateur.php';
/* Jump in ! */
$app->run();
