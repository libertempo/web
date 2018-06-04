<?php

use Psr\Container\ContainerInterface as C;
use DI\Container;
use Invoker\CallableResolver;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use function DI\get;
use function DI\create;
use function DI\autowire;

return [
    // Settings that can be customized by users
    'settings.httpVersion' => '1.1',
    'settings.responseChunkSize' => 4096,
    'settings.outputBuffering' => 'append',
    'settings.determineRouteBeforeAppMiddleware' => false,
    'settings.displayErrorDetails' => true,
    'settings.addContentLengthHeader' => true,
    'settings.routerCacheFile' => false,

    // Defaults settings
    'settings' => [
        'httpVersion' => get('settings.httpVersion'),
        'responseChunkSize' => get('settings.responseChunkSize'),
        'outputBuffering' => get('settings.outputBuffering'),
        'determineRouteBeforeAppMiddleware' => get('settings.determineRouteBeforeAppMiddleware'),
        'displayErrorDetails' => get('settings.displayErrorDetails'),
        'addContentLengthHeader' => get('settings.addContentLengthHeader'),
        'routerCacheFile' => get('settings.routerCacheFile'),
    ],
    'router' => create(Slim\Router::class)
        ->method('setContainer', get(Container::class))
        ->method('setCacheFile', get('settings.routerCacheFile')),
    Slim\Router::class => get('router'),
    'callableResolver' => autowire(CallableResolver::class),
    'environment' => function () {
        return new Slim\Http\Environment($_SERVER);
    },
    'request' => function (C $c) {
        return Request::createFromEnvironment($c->get('environment'));
    },
    'response' => function (C $c) {
        $headers = new Headers(['Content-Type' => 'application/json; charset=UTF-8']);
        $response = new Response(200, $headers);
        return $response->withProtocolVersion($c->get('settings')['httpVersion']);
    },
    'foundHandler' => create(\Slim\Handlers\Strategies\RequestResponse::class),

    // LibertAPI
    'badRequestHandler' => function (C $c) {
        return function (IRequest $request, IResponse $response) {
            $code = 400;
            $responseUpd = $response->withStatus($code);
            $data = [
                'code' => $code,
                'status' => 'fail',
                'message' => $responseUpd->getReasonPhrase(),
                'data' => 'Request Content-Type and Accept must be set on application/json only',
            ];

            return $responseUpd->withJson($data);
        };
    },
    'unauthorizedHandler' => function (C $c) {
        $code = 401;
        $response = $c->get('response');
        $responseUpd = $response->withStatus($code);
        $data = [
            'code' => $code,
            'status' => 'fail',
            'message' => $responseUpd->getReasonPhrase(),
            'data' => 'Bad API Key',
        ];

        return $response->withJson($data, 401);
    },
    'notFoundHandler' => function () {
        return function (IRequest $request, IResponse $response) {
            $code = 404;
            $responseUpd = $response->withStatus($code);
            return $responseUpd->withJson([
                'code' => $code,
                'status' => 'fail',
                'message' => $responseUpd->getReasonPhrase(),
                'data' => '« ' . $request->getUri()->getPath() . ' » is not a valid resource',
            ]);
        };
    },
    'errorHandler' => function () {
        return function (IRequest $request, IResponse $response, \Exception $exception) {
            $code = 500;
            $responseUpd = $response->withStatus($code);
            return $responseUpd->withJson([
                'code' => $code,
                'status' => 'error',
                'message' => $responseUpd->getReasonPhrase(),
                'data' => $exception->getMessage(),
            ]);
        };
    },
    'notAllowedHandler' => function () {
        return function (IRequest $request, IResponse $response, array $methods) {
            $methodString = implode(', ', $methods);
            $code = 405;
            $responseUpd = $response->withStatus($code);
            $data = [
                'code' => $code,
                'status' => 'fail',
                'message' => $responseUpd->getReasonPhrase(),
                'data' => 'Method on « ' . $request->getUri()->getPath() . ' » must be one of : ' . $methodString,
            ];

            return $responseUpd
                ->withHeader('Allow', $methodString)
                ->withJson($data);
        };
    },
    Doctrine\DBAL\Driver\Connection::class => function (C $c) {
        return $c->get('storageConnector');
    },
];
