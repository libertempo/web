<?php
namespace App\Libraries;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request;

/**
 * Classe de consommation de l'API ; is immutable
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @since 1.1
 */
final class ApiClient
{
    /**
     * @var ClientInterface Client de la requête
     */
    private $client;

    /**
     * @param ClientInterface $client Client de la requête
     *
     * @throws \RuntimeException if infrastructure pre-conditions aren't fulfilled
     */
    public function __construct(ClientInterface $client = null)
    {
        if (!extension_loaded('curl')) {
            if (false === ini_set('allow_url_fopen', true)) {
                throw new \RuntimeException('cURL or allow_url_fopen are required');
            }
        }
        // @todo s'appuyer sur l'injectableCreator pour n'avoir qu'une route et que le client n'aie pas conscience de la tambouille
        if (null === $client) {
            // mettre une methode getclient pour tester cette partie
            $baseURIApi = $_SERVER['HTTP_HOST'] . '/api/';
            $client = new \GuzzleHttp\Client([
                'base_uri' => $baseURIApi,
            ]);
        }
        $this->client = $client;
    }

    /**
     * @param string $uri URI relative de la ressource
     */
    public function get($uri)
    {
        return $this->request('GET', $uri);
    }

    private function request($method, $uri)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
        $request = new Request($method, $uri, $headers);
        $response = $this->client->send($request);

        if (500 === $response->getStatusCode()) {
            throw new \ErrorException('Server error');
        }

        $body = json_decode($response->getBody(), true);
        if (null === $body) {
            throw new \ErrorException('Response isn\'t json');
        }

        return $body;
    }

    /**
     * @todo pour garantir l'immuabilité
     */
    public function __clone()
    {
        // throw ...
    }
}
