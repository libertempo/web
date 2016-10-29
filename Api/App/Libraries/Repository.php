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
abstract class Repository
{
    /**
     * @var \Api\App\Libraries\Dao $dao Data Access Object
     */
    protected $dao;

    public function __construct(\Api\App\Libraries\Dao $dao)
    {
        $this->dao = $dao;
    }
}
