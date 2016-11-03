<?php
namespace Api\App\Libraries;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use \Api\App\Libraries\Repository;

/**
 * Contrôleur principal
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté par personne
 * Ne devrait contacter personne
 */
abstract class Controller
{
    /**
     * @var ServerRequestInterface Requête HTTP
     */
    protected $request;

    /**
     * @var ResponseInterface Réponse HTTP
     */
    protected $response;

    /**
     * @var Repository Repository de la ressource
     */
    protected $repository;

    public function __construct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Repository $repository
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->repository = $repository;
    }
}
