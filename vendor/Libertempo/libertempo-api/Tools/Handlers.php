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
};
$container['unauthorizedHandler'] = function () {
    return function (IRequest $request, IResponse $response) {
        $code = 401;
        $responseUpd = $response->withStatus($code);
        $data = [
            'code' => $code,
            'status' => 'fail',
            'message' => $responseUpd->getReasonPhrase(),
            'data' => 'Bad API Key',
        ];

        return $response->withJson($data, 401);
    };
};

$container['notFoundHandler'] = function () {
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
};

$container['notAllowedHandler'] = function () {
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
};

$container['errorHandler'] = function () {
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
};
