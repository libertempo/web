<?php
namespace Tests\Units\App\Libraries;

use \App\Libraries\ApiClient as _ApiClient;

class ApiClient extends \Tests\Units\TestUnit
{
    private $client;

    private $response;

    public function beforeTestMethod($testMethod)
    {
        $this->client = new \mock\GuzzleHttp\Client();
        $this->response = new \mock\GuzzleHttp\Psr7\Response();
        $this->client->getMockController()->send = $this->response;

        switch ($testMethod) {
            case 'testConstructWithCurl':
            case 'testConstructWithoutCurlWithFopen':
            case 'testConstructWithoutCurlWithoutFopen':
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

        $api = new _ApiClient('', $this->client);

        $this->object($api)->isInstanceOf('\App\Libraries\ApiClient');
    }

    /**
     * Test de la construction quand l'extension curl n'est pas chargée
     * mais que la directive sur fopen est activée
     */
    public function testConstructWithoutCurlWithFopen()
    {
        $this->function->extension_loaded = false;
        $this->function->ini_set = true;

        $api = new _ApiClient('', $this->client);

        $this->object($api)->isInstanceOf('\App\Libraries\ApiClient');
    }

    /**
     * Test de la construction quand ni l'extension curl n'est chargée
     * ni la directive est activée
     */
    public function testConstructWithoutCurlWithoutFopen()
    {
        $this->function->extension_loaded = false;
        $this->function->ini_set = false;

        $this->exception(function () {
            new _ApiClient('', $this->client);
        })->isInstanceOf('\RuntimeException');
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Test de la requête GET avec une erreur serveur
     */
    public function testGetServerError()
    {
        $this->response->getMockController()->getStatusCode = 500;

        $api = new _ApiClient('', $this->client);

        $this->exception(function () use ($api) {
            $api->get();
        })->isInstanceOf('\ErrorException');
    }

    /**
     * Test de la requête GET avec une réponse au mauvais format
     */
    public function testGetIsNotJson()
    {
        $this->response->getMockController()->getBody = '';

        $api = new _ApiClient('', $this->client);

        $this->exception(function () use ($api) {
            $api->get();
        })->isInstanceOf('\ErrorException');
    }

    /**
     * Test de la requête GET quand tout s'est bien passé
     */
    public function testGetOk()
    {
        $data = [
            'code' => '',
            'status' => '',
            'message' => '',
            'data' => '',
        ];
        $this->response->getMockController()->getBody = json_encode($data);

        $api = new _ApiClient('', $this->client);
        $get = $api->get();

        $this->array($get)
            ->hasKey('code')
            ->hasKey('status')
            ->hasKey('message')
            ->hasKey('data')
        ;
    }
}
