<?php
namespace LibertAPI\Tools\Libraries;

use Doctrine\DBAL\Query;
use Doctrine\DBAL\Driver;

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
    public function __construct(Driver\Connection $storageConnector)
    {
        $this->storageConnector = $storageConnector;
        $this->queryBuilder = $storageConnector->createQueryBuilder();
        $this->queryBuilder->from($this->getTableName());
    }

    /**
    * @var Driver\Connection Connecteur à la BDD
    */
    protected $storageConnector;

    /**
    * @var Query\QueryBuilder
    */
    protected $queryBuilder;

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
     * Met à jour une ressource
     *
     * @param array $data Données à mettre à jour
     * @param int $id Id de l'élément à mettre à jour
     */
    abstract public function put(array $data, $id);

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Détruit une nouvelle ressource
     *
     * @param int $id Id de l'élément à supprimer
     *
     * @return int Nombre d'éléments affectés
     */
    abstract public function delete($id);

    /**
     * Retourne le nom de la table
     *
     * @return string
     */
    abstract protected function getTableName();

    /**
     * Initie une transaction
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->storageConnector->beginTransaction();
    }

    /**
     * Valide une transaction
     *
     * @return bool
     */
    public function commit()
    {
        return $this->storageConnector->commit();
    }

    /**
     * Annule une transaction
     *
     * @return bool
     */
    public function rollback()
    {
        return $this->storageConnector->rollBack();
    }
}
