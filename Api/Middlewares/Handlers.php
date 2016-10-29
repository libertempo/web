<?php
/*
 * Simple déclaration des handlers à injecter dans le serveur Slim
 *
 * La convention de nommage est de mettre les routes au pluriel
 */
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
            require_once CONFIG_PATH . 'dbconnect.php';
            $storageConnector = new \PDO(
                'mysql:host=localhost;dbname=' . $mysql_database,
                $mysql_user,
                $mysql_pass
            );

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
                $dic->errorHandler,
                $request,
                $response,
                $e
            );
        }
    };
};
