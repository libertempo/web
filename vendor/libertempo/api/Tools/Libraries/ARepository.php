<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Libraries;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Query\QueryBuilder;
use \LibertAPI\Tools\Exceptions\UnknownResourceException;

/**
 * Garant de la cohérence métier de l'entité en relation.
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
abstract class ARepository
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
    * @var QueryBuilder
    */
    protected $queryBuilder;

    /**
     * Retourne une ressource unique
     *
     * @param int $id Id potentiel de ressource (Ne peut pas être typecasté tant que utilisateur n'a pas d'id, ou que php7.2 n'est pas activé)
     *
     * @return AEntite
     * @throws UnknownResourceException Si $id n'est pas dans le domaine de définition
     */
    public function getOne($id) : AEntite
    {
        $this->queryBuilder->select('*');
        $this->setWhere(['id' => $id]);
        $res = $this->queryBuilder->execute();

        $data = $res->fetch(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            throw new UnknownResourceException('#' . $id . ' is not a valid resource');
        }

        $entiteClass = $this->getEntiteClass();

        return new $entiteClass($this->getStorage2Entite($data));
    }

    /**
     * Retourne une liste de ressource correspondant à des critères
     *
     * @param array $parametres
     *
     * @return array [$objet]
     * @throws \UnexpectedValueException Si les critères ne sont pas pertinents
     */
    public function getList(array $parametres) : array
    {
        $this->queryBuilder->select('*');
        $this->setWhere($this->getParamsConsumer2Storage($parametres));
        $res = $this->queryBuilder->execute();

        $data = $res->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            throw new \UnexpectedValueException('No resource match with these parameters');
        }

        $entiteClass = $this->getEntiteClass();

        $entites = array_map(
            function ($value) use ($entiteClass) {
                return new $entiteClass($this->getStorage2Entite($value));
            },
            $data
        );

        return $entites;
    }

    abstract protected function getEntiteClass() : string;

    /**
     * Effectue le mapping des éléments venant du stockage pour qu'ils soient compréhensibles pour l'Entité
     *
     * @param array $dataStorage
     *
     * @return array
     */
    abstract protected function getStorage2Entite(array $dataStorage);

    /**
     * Effectue le mapping des recherches du consommateur de l'API pour qu'elles
     * soient traitables par le stockage
     *
     * Essentiel pour séparer / traduire les contextes Client / stockage
     *
     * @param array $paramsConsumer Paramètres reçus
     *
     * @return array
     */
    abstract protected function getParamsConsumer2Storage(array $paramsConsumer) : array;

    /**
     * Poste une ressource unique
     *
     * @param array $data Données à poster
     *
     * @return int Id de la ressource nouvellement insérée
     */
    public function postOne(array $data) : int
    {
        $entiteClass = $this->getEntiteClass();
        $entite = new $entiteClass([]);
        $entite->populate($data);
        $this->queryBuilder->insert($this->getTableName());
        $this->setValues($this->getEntite2Storage($entite));
        $this->queryBuilder->execute();

        return (int) $this->storageConnector->lastInsertId();
    }

    /**
     * Définit les values à insérer
     *
     * @param array $values
     */
    abstract protected function setValues(array $values);

    /**
     * Met à jour une ressource unique
     *
     * @param int $id ID de la ressource (Ne peut pas être typecasté tant que utilisateur n'a pas d'id, ou que php7.2 n'est pas activé)
     * @param array $data Données à mettre à jour
     *
     * @returns AEntite Une entité résultante de l'opération
     */
    public function putOne($id, array $data) : AEntite
    {
        $entite = $this->getOne($id);
        $entite->populate($data);
        $this->queryBuilder->update($this->getTableName());
        $this->setSet($this->getEntite2Storage($entite));
        $this->setWhere(['id', $entite->getId()]);

        $this->queryBuilder->execute();

        return $entite;
    }

    abstract protected function setSet(array $parametres);

    /**
     * Définit les filtres à appliquer à la requête
     *
     * @param array $parametres
     * @example [filter => []]
     */
    abstract protected function setWhere(array $parametres);

    /**
     * Effectue le mapping des éléments venant de l'entité pour qu'ils soient compréhensibles pour le stockage
     *
     * @param AEntite $entite
     *
     * @return array
     */
    abstract protected function getEntite2Storage(AEntite $entite) : array;

    /**
     * Détruit une ressource unique
     *
     * @param AEntite $entite
     */
    public function deleteOne(int $id) : int
    {
        $entite = $this->getOne($id);
        $this->queryBuilder->delete($this->getTableName());
        $this->setWhere(['id' => $entite->getId()]);
        $res = $this->queryBuilder->execute();
        $entite->reset();

        return $res->rowCount();
    }

    /**
     * Retourne le nom de la table
     */
    abstract protected function getTableName() : string;

    /**
     * Initie une transaction
     */
    final protected function beginTransaction() : bool
    {
        return $this->storageConnector->beginTransaction();
    }

    /**
     * Valide une transaction
     */
    final protected function commit() : bool
    {
        return $this->storageConnector->commit();
    }

    /**
     * Annule une transaction
     */
    final protected function rollback() : bool
    {
        return $this->storageConnector->rollBack();
    }
}
