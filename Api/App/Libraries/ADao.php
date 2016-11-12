<?php
namespace Api\App\Libraries;

/**
 * Classe de base des DAO
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté par personne
 * Ne devrait contacter personne
 */
abstract class ADao
{
    /**
     * @var \PDO Connecteur à la BDD
     */
    protected $storageConnector;

    public function __construct(\PDO $storageConnector)
    {
        $this->storageConnector = $storageConnector;
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Retourne une ressource unique
     *
     * @param int $id Id potentiel de ressource
     *
     * @return array, vide si $id n'est pas dans le domaine de définition
     */
    abstract public function getById($id);

    /**
     * Retourne une liste de ressource correspondant à des critères
     *
     * @param array $parametres
     * @example [filter => [], lt => 23, limit => 4]
     *
     * @return array, vide si les critères ne sont pas pertinents
     */
    abstract public function getList(array $parametres);

    /*************************************************
     * POST
     *************************************************/

    /**
     * Poste une nouvelle ressource
     *
     * @param array $data Données à insérer
     *
     * @return int Id de la ressource nouvellement créée
     */
    abstract public function post(array $data);

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Poste une nouvelle ressource
     *
     * @param array $data Données à mettre à jour
     * @param int $id Id de l'élément à mettre à jour
     */
    abstract public function put(array $data, $id);

    /**
     * Retourne le nom de la table
     *
     * @return string
     */
    abstract protected function getTableName();
}
