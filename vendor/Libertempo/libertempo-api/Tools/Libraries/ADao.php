<?php declare(strict_types = 1);
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
        $this->queryBuilder->from($this->getTableName(), 'current');
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
     * @return AEntite
     * @throws \DomainException si $id n'est pas dans le domaine de définition
     */
    abstract public function getById(int $id) : AEntite;

    /**
     * Effectue le mapping des éléments venant de la DAO pour qu'ils soient compréhensibles pour l'Entité
     *
     * @param array $dataDao
     *
     * @return array
     */
    abstract protected function getStorage2Entite(array $dataDao);

    /**
     * Retourne une liste de ressource correspondant à des critères
     *
     * @param array $parametres
     * @example [filter => []]
     *
     * @return array, vide si les critères ne sont pas pertinents
     */
    abstract public function getList(array $parametres) : array;

    /*************************************************
     * POST
     *************************************************/

    /**
     * Poste une nouvelle ressource
     *
     * @param AEntite $entite
     *
     * @return int Id de la ressource nouvellement créée
     */
    abstract public function post(AEntite $entite) : int;

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Met à jour une ressource
     *
     * @param AEntite $entite
     */
    abstract public function put(AEntite $entite);

    /**
     * Effectue le mapping des éléments venant de l'entité pour qu'ils soient compréhensibles pour la DAO
     *
     * @param AEntite $entite
     *
     * @return array
     */
    abstract protected function getEntite2Storage(AEntite $entite) : array;

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
    abstract public function delete(int $id) : int;

    /**
     * Retourne le nom de la table
     *
     * @return string
     */
    abstract protected function getTableName() : string;

    /**
     * Initie une transaction
     *
     * @return bool
     */
    public function beginTransaction() : bool
    {
        return $this->storageConnector->beginTransaction();
    }

    /**
     * Valide une transaction
     *
     * @return bool
     */
    public function commit() : bool
    {
        return $this->storageConnector->commit();
    }

    /**
     * Annule une transaction
     *
     * @return bool
     */
    public function rollback() : bool
    {
        return $this->storageConnector->rollBack();
    }
}
