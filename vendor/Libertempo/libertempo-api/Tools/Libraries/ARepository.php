<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Libraries;

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
    /**
     * @var ADao $dao Data Access Object
     */
    protected $dao;

    public function __construct(ADao $dao)
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
     * @return AEntite
     * @throws \DomainException Si $id n'est pas dans le domaine de définition
     */
    public function getOne(int $id) : AEntite
    {
        return $this->dao->getById($id);
    }

    /**
     * Retourne une liste de ressource correspondant à des critères
     *
     * @param array $parametres
     *
     * @return array [$objetId => $objet]
     * @throws \UnexpectedValueException Si les critères ne sont pas pertinents
     */
    public function getList(array $parametres) : array
    {
        return $this->dao->getList($this->getParamsConsumer2Dao($parametres));
    }

    /**
     * Effectue le mapping des recherches du consommateur de l'API pour qu'elles
     * soient traitables par la DAO
     *
     * Essentiel pour séparer / traduire les contextes Client / DAO
     *
     * @param array $paramsConsumer Paramètres reçus
     *
     * @return array
     */
    abstract protected function getParamsConsumer2Dao(array $paramsConsumer) : array;

    /*************************************************
     * POST
     *************************************************/

    /**
     * Poste une ressource unique
     *
     * @param array $data Données à poster
     * @param AEntite $entite [Vide par définition]
     *
     * @return int Id de la ressource nouvellement insérée
     */
    public function postOne(array $data, AEntite $entite)
    {
        $entite->populate($data);
        return $this->dao->post($entite);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Met à jour une ressource unique
     *
     * @param array $data Données à mettre à jour
     * @param AEntite $entite
     */
    public function putOne(array $data, AEntite $entite)
    {
        $entite->populate($data);
        $this->dao->put($entite);
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * Détruit une ressource unique
     *
     * @param AEntite $entite
     */
    abstract public function deleteOne(AEntite $entite);
}
