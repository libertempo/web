<?php
namespace App\Libraries;

use App\Exceptions\MissingArgumentException;

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
abstract class ARepository
{
    /**
     * @var \App\Libraries\ADao $dao Data Access Object
     */
    protected $dao;

    public function __construct(\App\Libraries\ADao $dao)
    {
        $this->dao = $dao;
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Retourne une ressource unique
     *
     * @param int $id Id potentiel de ressource
     *
     * @return \App\Libraries\AModel
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
     * Effectue le mapping des éléments venant de la DAO pour qu'ils soient compréhensibles pour le Modèle
     *
     * @param array $dataDao
     *
     * @return array
     */
    abstract protected function getDataDao2Model(array $dataDao);

    /**
     * Effectue le mapping des recherches du consommateur de l'API pour qu'elles
     * soient traitables par la DAO
     *
     * Essentiel pour séparer / traduire les contextes Client / DAO
     *
     * @param array $paramsConsumer Paramètres reçus
     * @example [offset => 4, start-after => 23, filter => 'name::chapo|status::1,3']
     *
     * @return array
     */
    abstract protected function getParamsConsumer2Dao(array $paramsConsumer);

    /**
     * Effectue le mapping des éléments venant du modèle pour qu'ils soient compréhensibles pour la DAO
     *
     * @param AModel $model
     *
     * @return array
     */
    abstract protected function getModel2DataDao(AModel $model);

    /*************************************************
     * POST
     *************************************************/

    /**
     * Poste une ressource unique
     *
     * @param array $data Données à poster
     * @param AModel $model [Vide par définition]
     *
     * @return int Id de la ressource nouvellement insérée
     * @throws MissingArgumentException Si un élément requis n'est pas présent
     * @throws \DomainException Si un élément de la ressource n'est pas dans le bon domaine de définition
     */
    abstract public function postOne(array $data, AModel $model);

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Met à jour une ressource unique
     *
     * @param array $data Données à mettre à jour
     * @param AModel $model
     *
     * @throws MissingArgumentException Si un élément requis n'est pas présent
     * @throws \DomainException Si un élément de la ressource n'est pas dans le bon domaine de définition
     */
    abstract public function putOne(array $data, AModel $model);

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Détruit une ressource unique
     *
     * @param AModel $model
     */
    abstract public function deleteOne(AModel $model);
}
