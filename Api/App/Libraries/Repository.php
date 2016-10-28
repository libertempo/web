<?php
namespace Api\App\Libraries;

/**
 * Garant de la cohérence métier du modèle en relation.
 * Autrement dit, c'est lui qui va chercher les données (dépendances comprises),
 * pour former un Domain model bien formé
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
class Repository
{
    /**
     * Connecteur à la BDD
     */
    protected $storageConnector;

    public function __construct($storageConnector)
    {
        $this->storageConnector = $storageConnector;
    }
}
