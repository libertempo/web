<?php
namespace Tests\Units\App\Libraries;

use \App\Libraries\ApiClient as _ApiClient;

/**
 * Classe de test du connecteur API
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 * @since 1.9
 */
class ApiClient extends \Tests\Units\TestUnit
{
    /**
     * @var \mock\GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \mock\GuzzleHttp\Psr7\Response
     */
    private $response;

    /**
     * @var array
     */
    private $defaultOptions = [
        'headers' => [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ],
    ];

    public function beforeTestMethod($testMethod)
    {
        parent::beforeTestMethod($testMethod);
        $this->client = new \mock\GuzzleHttp\Client();
        $this->response = new \mock\GuzzleHttp\Psr7\Response();
        $this->calling($this->client)->request = $this->response;

        switch ($testMethod) {
            case 'testConstructWithCurl':
                break;

            default:
                $this->function->extension_loaded = true;
                $this->function->ini_set = true;
                break;
        }
    }

    /**
     * Test de la construction quand l'extension curl est chargée
     */
    public function testConstructWithCurl()
    {
        $this->function->extension_loaded = true;

        $api = new _ApiClient($this->client);

        $this->object($api)->isInstanceOf('\App\Libraries\ApiClient');
    }

    /**
     * Test de la construction quand ni l'extension curl n'est chargée
     */
    public function testConstructWithoutCurl()
    {
        $this->function->extension_loaded = false;

        $this->exception(function () {
            new _ApiClient($this->client);
        })->isInstanceOf('\RuntimeException');
    }

    /*************************************************
     * CAS D'ERREUR
     *************************************************/

    public function testGetServerError()
    {
        return $this->requestServerError(function () {
            $api = new _ApiClient($this->client);
            return $api->get('', 'dragibus');
        });
    }

    public function testAuthentificationServerError()
    {
        return $this->requestServerError(function () {
            $api = new _ApiClient($this->client);
            return $api->authentifyDbConges('', '');
        });
    }

    private function requestServerError(callable $closure)
    {
        $request = new \mock\GuzzleHttp\Psr7\Request('GET', '');
        $response = new \mock\GuzzleHttp\Psr7\Response();

        $this->calling($this->client)->request = function () use ($request, $response) {
            throw new \GuzzleHttp\Exception\ServerException('', $request, $response);
        };

        $this->exception(function () use ($closure) {
            $closure();
        })->isInstanceOf(\RuntimeException::class);
    }

    public function testGetClientError()
    {
        return $this->requestClientError(function () {
            $api = new _ApiClient($this->client);
            return $api->get('', 'dragibus');
        });
    }

    public function testAuthentificationClientError()
    {
        return $this->requestClientError(function () {
            $api = new _ApiClient($this->client);
            return $api->authentifyDbConges('', '');
        });
    }

    private function requestClientError(callable $closure)
    {
        $request = new \mock\GuzzleHttp\Psr7\Request('GET', '');
        $response = new \mock\GuzzleHttp\Psr7\Response();

        $this->calling($this->client)->request = function () use ($request, $response) {
            throw new \GuzzleHttp\Exception\ClientException('', $request, $response);
        };

        $this->exception(function () use ($closure) {
            $closure();
        })->isInstanceOf(\LogicException::class);
    }

    public function testGetIsNoJson()
    {
        return $this->requestIsNotJson(function () {
            $api = new _ApiClient($this->client);
            return $api->get('', 'dragibus');
        });
    }

    public function testAuthentificationIsNotJson()
    {
        return $this->requestIsNotJson(function () {
            $api = new _ApiClient($this->client);
            return $api->authentifyDbConges('', '');
        });
    }

   private function requestIsNotJson(callable $closure)
   {
       $this->calling($this->response)->getBody = 'Crocodile';

       $this->exception(function () use ($closure) {
           $closure();
       })->isInstanceOf(\RuntimeException::class);
   }

   /*************************************************
    * CAS NORMAUX
    *************************************************/

    public function testGetOk()
    {
        $options = array_merge_recursive([
            'headers' => [
                'Token' => 'dragibus',
            ]],
            $this->defaultOptions
        );
        $this->requestOk(
            function () use ($options) {
                $api = new _ApiClient($this->client);
                return $api->get('', 'dragibus');
            },
            'GET',
            '',
            $options
        );
    }

    public function testAuthentificationOk()
    {
        $options = array_merge_recursive([
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode('Kevin:Stuart'),
            ]],
            $this->defaultOptions
        );
        $this->requestOk(
            function () use ($options) {
                $api = new _ApiClient($this->client);
                return $api->authentifyDbConges('Kevin', 'Stuart');
            },
            'GET',
            'authentification',
            $options
        );
    }

    private function requestOk(callable $closure, $method, $uri, array $options)
    {
        $data = [
            'code' => '',
            'status' => '',
            'message' => '',
            'data' => '',
        ];
        $this->response->getMockController()->getBody = json_encode($data);
        $request = $closure();

        $this->mock($this->client)->call('request')->withIdenticalArguments($method, $uri, $options)->once();

        $this->string($request->code);
        $this->string($request->status);
        $this->string($request->message);
        $this->string($request->data);
    }

    public function testClone()
    {
        $api = new _ApiClient($this->client);

        $this->exception(function () use ($api) {
            clone $api;
        })->isInstanceOf(\LogicException::class);
    }
}
