<?php
/**
 * API de Libertempo
 * @version 0.1
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$container['notFoundHandler'] = function () {
    return function (ServerRequestInterface $request, ResponseInterface $response, \Exception $exception) {
        $data = [
            'code' => 404,
            'status' => 'error',
            'message' => 'Not Found',
            'data' => '« ' . $request->getUri()->getPath() . ' » is not a valid resource name ',
        ];
        return $response->withJson($data, 404);
    };
};
$container['errorHandler'] = function () {
    return function (ServerRequestInterface $request, ResponseInterface $response, \Exception $exception) {
        $data = [
            'code' => 500,
            'status' => 'fail',
            'message' => 'Internal Server Error',
            'data' => $exception->getMessage(),
        ];
        return $response->withJson($data, 500);
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

$app = new \Slim\App($container);

$app->post('/hello', function (ServerRequestInterface $request, ResponseInterface $response, $args) {
    $a = $response->withJson(['test' => 'baba'], 200);
    return $a;
});

/**
 * creation des controllers
 * // creation des repositories
 * creation des acces db
 * creation des dao
 * creation des domain models
 * creation des collections
 */

 /**
  * querystring que pour GET et pour la recherche d'éléments !!
  */


/**
 * PUT planningsIdPut
 * Summary: Met à jour un planning d&#39;employé
 * Notes:
 * Output-Formats: [application/json]
 */
$app->put('/plannings/{id}', function($request, $response, $args) {
    $headers = $request->getHeaders();
    /* Check api key and error access : 401 */
    // $planning->putOne($id)

    $name = $args['name'];    $status = $args['status'];

    $response->write('How about implementing planningsIdPut as a PUT method ?');
    return $response;
});
// injection de la request et de la response dans le controleur, pour le test

/**
 * sinon on peut faire par groupe, si c'est possible :
 * $app->group('/api/plannings', function () {
 *      $this->get('/');
 *      $this->post('/');
 *      $this->group('/{id}', function () {
 *          $this->get('/');
 *          $this->put('/');
 *          ...
 *      });
 *      ...
 *      $this->any(); // return 405
  * });
  *
  *
  * si ce n'est pas possible, on fait un fallback :
  *
  * $app->any('/api/{resource:[a-z_]+}', function () {
  * // si pas droit d'acces, 403
  * // to StudlyCaps
  * // drop the plural
  * // si pas de ressource, 404
  * // get method by request
  * // appel de la methode sur la ressource, si methode inexistant, 405
  * })
  *
  * mais pour tout ce qui peut l'être, on précise en amont
  * '/api/{resource:[a-z_]+}/{resourceId:[0-9]+}', '/api/maRessource/{resourceId:[0-9]+}/dessous', ...
 */


/**
* GET planningsIdGet
* Summary: Planning d&#39;employé
* Notes:
* Output-Formats: [application/json]
*/
$app->get('/plannings/{id}', function($request, $response, $args) {
    $headers = $request->getHeaders();
    /* Check api key and error access : 401 */
    // $planning->getOne($id)

    $response->withJson('How about implementing planningsIdGet as a GET method ?');
    return $response;
});


/**
 * GET planningsGet
 * Summary: Liste des plannings d&#39;employés
 * Notes:
 * Output-Formats: [application/json]
 */
$app->get('/plannings', function($request, $response, $args) {
    $headers = $request->getHeaders();
    $queryParams = $request->getQueryParams();
    $statuts = $queryParams['statuts'];
    /* Check api key and error access : 401 */
    // $planning->getList()
    var_dump('query', $queryParams);


    $response->write('How about implementing planningsGet as a GET method ?');
    var_dump($response);
    return $response;
});

/**
 * POST planningsPost
 * Summary: Ajoute un nouveau planning
 * Notes:
 * Output-Formats: [application/json]
 */
$app->post('/plannings', function($request, $response, $args) {
    $headers = $request->getHeaders();
    /* Check api key and error access : 401 */
    // $planning->postOne() : 201 on success, return resource link in header location

    $name = $args['name'];    $statut = $args['statut'];

    $response->write('How about implementing planningsPost as a POST method ?');
    return $response;
});

$app->get('/{ressource:[a-z_]+}/{ressourceId:[0-9]+}', function(ServerRequestInterface $request, ResponseInterface $response, $args) {
    $headers = $request->getHeaders();
    /* Check api key and error access : 401 */
    // $planning->putOne($id)

    $response->write('Voici un Nope avec la ressource ' . $args['ressource'] . ' de lid ' . $args['ressourceId']);
    return $response;
});

$app->get('/{ressource:[a-z_]+}', function(ServerRequestInterface $request, ResponseInterface $response, $args) {
    // snake to StudlyCaps
    // drop the plural
    $class = '\Api\App\\' . $args['ressource'] . '\Controller';
    if (!class_exists($class, true)) {
        $data = [
            'code' => 404,
            'status' => 'error',
            'message' => '« ' . $args['ressource'] . ' » is not a valid resource name',
            'data' => $class,
        ];
        return $response->withJson($data, 404);
        // 404 (pour la collection. Pour la ressource, on en aura aussi un plus loin)
    }
    if (!is_callable([$class, 'get'])) {
        // 405
    }

    try {
        //* Check api key : 401 *
        //$controller = new $class($request, $response);
        // si pas droit d'acces id user : 403
        //return $controller->get();
    } catch (\Exception $e) {
        // cas d'erreur normalise
    }



    $headers = $request->getHeaders();
    // $planning->putOne($id)
});



$app->run();
