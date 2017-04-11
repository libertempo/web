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
     * @var string URI relative vers la ressource à atteindre
     */
    private $uri;

    /**
     * @var ClientInterface Client de la requête
     */
    private $client;

    /**
     * @param string URI de la ressource
     * @param ClientInterface $client Client de la requêTypeError
     *
     * @throws \RuntimeException if infrastructure pre-conditions aren't fulfilled
     */
    public function __construct($uri, ClientInterface $client = null)
    {
        if (!extension_loaded('curl')) {
            if (false === ini_set('allow_url_fopen', true)) {
                throw new \RuntimeException('cURL or allow_url_fopen are required');
            }
        }
        $this->uri = $uri;
        if (null === $client) {
            $baseURIApi = $_SERVER['HTTP_HOST'] . '/';// api/
            $client = new \GuzzleHttp\Client([
                'base_uri' => $baseURIApi,
            ]);
        }
        $this->client = $client;
        /*
         * $request = new Request('PUT', 'http://httpbin.org/put');
         * $response = $client->send($request, ['timeout' => 2]);
         */
    }

    public function get()
    {
        $request = new Request('GET', 'install/index.php');
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

    public function __clone()
    {
        // throw ...
    }
}
