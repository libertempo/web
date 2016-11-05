<?php
namespace Api\App\Libraries;

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
     * @var Repository Repository de la ressource
     */
    protected $repository;

    public function __construct(Repository $repository) {
        $this->repository = $repository;
    }
}
