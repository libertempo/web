<?php
namespace Api\App\Libraries;

use \Api\App\Libraries\Repository;
use \Slim\Interfaces\RouterInterface as IRouter;

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
     * @var Repository Repository de la ressource
     */
    protected $repository;

    /**
     * @var IRouter Routeur de l'application
     */
    protected $router;

    public function __construct(Repository $repository, IRouter $router) {
        $this->repository = $repository;
        $this->router = $router;
    }
}
