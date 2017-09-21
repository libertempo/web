<?php
namespace App\Libraries;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception;

/**
 * Classe de consommation de l'API ; is immutable
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 * @since 1.9
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
    public function __construct(ClientInterface $client)
    {
        if (!extension_loaded('curl')) {
            if (false === ini_set('allow_url_fopen', true)) {
                throw new \RuntimeException('cURL or allow_url_fopen are required');
            }
        }
        $this->client = $client;
    }

    /**
     * Effectue l'ordre Http GET
     *
     * @param string $uri URI relative de la ressource
     *
     * @return \stdClass Au format Jsend
     */
    public function get($uri)
    {
        return $this->request('GET', $uri, []);
    }

    /**
     * Récupère un token de l'API avec le connecteur DBConges
     *
     * @param string $login Login de l'utilisateur LT
     * @param string $password MDP de l'utilisateur LT
     *
     * @return \stdClass Au format Jsend
     */
    public function authentifyDbConges($login, $password)
    {
        return $this->authentifyCommon($login, $password);
    }

    /**
     * Récupère un token de l'API avec le connecteur tierce
     *
     * @param string $login Login de l'utilisateur LT
     *
     * @return \stdClass Au format Jsend
     */
    public function authentifyThirdParty($login)
    {
        return $this->authentifyCommon($login, 'none');
    }

    /**
     * Récupère un token de l'API pour les futurs échanges
     *
     * @param string $login Login de l'utilisateur LT
     * @param string $password MDP de l'utilisateur LT
     *
     * @return \stdClass Au format Jsend
     */
    private function authentifyCommon($login, $password)
    {
        $options = [
            'headers' => [
                'Authorization' => 'Basic ' . base64_encode($login . ':' . $password),
            ],
        ];
        return $this->request('GET', 'authentification', $options);
    }

    /**
     * Effectue une requête HTTP
     *
     * @param string $method Ordre HTTP
     * @param string $uri URI relative de la ressource
     * @param array $options Options de requête
     * @example ['headers' => [], 'body' => []]
     *
     * @return \stdClass Au format Jsend
     * @throws \LogicException Si la requête est mal formée (Http4XX)
     */
    private function request($method, $uri, array $options)
    {
        $json = 'application/json';
        $options = array_merge_recursive($options, [
            'headers' => [
                'Content-Type' => $json,
                'Accept' => $json,
            ],
        ]);
        try {
            $response = $this->client->request($method, $uri, $options);
        } catch (Exception\ServerException $e) {
            throw new \RuntimeException('Erreur serveur : '. $this->formatError($e));
        } catch (Exception\ClientException $e) {
            throw new \LogicException('Erreur client : '. $this->formatError($e));
        }

        $body = json_decode($response->getBody(), false);
        if (null === $body) {
            throw new \RuntimeException('La réponse n\'est pas du JSON');
        }

        return $body;
    }

    /**
     * Formate les erreurs pour afficher les informations nécessaires à la correction
     *
     * @param \Exception $exception
     *
     * @return string
     */
    private function formatError(\Exception $exception)
    {
        return 'Request -> ' . Psr7\str($exception->getRequest()) . ' | Response <- ' . Psr7\str($exception->getResponse());
    }

    /**
     * Clonage interdit
     */
    public function __clone()
    {
        throw new \LogicException('Clonage interdit');
    }
}
