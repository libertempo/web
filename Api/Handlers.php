<?php
/*
 * Simple déclaration des handlers à injecter dans le serveur Slim
 */
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**************************
 * Handlers par défaut
 **************************/
$container['badRequestHandler'] = function () {
    return function (IRequest $request, IResponse $response) {
        $data = [
            'code' => 400,
            'status' => 'error',
            'message' => 'Bad Request',
            'data' => 'Request Content-Type and Accept must be set on application/json only',
        ];

        return $response->withJson($data, 400);
    };
};
$container['unauthorizedHandler'] = function () {
    return function (IRequest $request, IResponse $response) {
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
    return function (IRequest $request, IResponse $response) {
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
    return function (IRequest $request, IResponse $response) {
        return $response->withJson([
            'code' => 404,
            'status' => 'error',
            'message' => 'Not Found',
            'data' => '« ' . $request->getUri()->getPath() . ' » is not a valid resource',
        ], 404);
    };
};

$container['notAllowedHandler'] = function () {
    return function (IRequest $request, IResponse $response, array $methods) {
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
    return function (IRequest $request, IResponse $response, \Exception $exception) {
        return $response->withJson([
            'code' => 500,
            'status' => 'fail',
            'message' => 'Internal Server Error',
            'data' => $exception->getMessage(),
        ], 500);
    };
};
