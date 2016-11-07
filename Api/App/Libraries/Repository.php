<?php
namespace Api\App\Libraries;

use Api\App\Exceptions\MissingArgumentException;

/**
 * Garant de la cohérence métier du modèle en relation.
 * Autrement dit, c'est lui qui va chercher les données (dépendances comprises),
 * pour construire un Domain model bien formé
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté par personne
 * Ne devrait contacter personne
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

    /**
     * Retourne une ressource unique
     *
     * @param int $id Id potentiel de ressource
     *
     * @return \Api\App\Libraries\Model
     * @throws \DomainException Si $id n'est pas dans le domaine de définition
     */
    abstract public function getOne($id);

    /**
     * Retourne une liste de ressource correspondant à des critères
     *
     * @param array $parametres
     * @example [offset => 4, start-after => 23, filter => 'name::chapo|status::1,3']
     *
     * @return array [$objetId => $objet]
     * @throws \UnexpectedValueException Si les critères ne sont pas pertinents
     */
    abstract public function getList(array $parametres);

    /**
     * Poste une ressource unique
     *
     * @param array $data Données à poster
     *
     * @return int Id de la ressource nouvellement insérée
     * @throws MissingArgumentException Si un élément requis n'est pas présent
     * @throws \DomainException Si un élément de la ressource n'est pas dans le bon domaine de définition
     */
    abstract public function postOne(array $data);
}
