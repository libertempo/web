<?php
namespace LibertAPI\Planning\Creneau;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Tests\Units\Planning\Repository
 */
class CreneauRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * @inheritDoc
     *
     * @param int $planningId Contrainte de recherche sur le planning
     */
    public function getOne($id, $planningId = -1)
    {
        return $this->dao->getById((int) $id, $planningId);
    }

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Dao(array $paramsConsumer)
    {
        $results = [];
        if (!empty($paramsConsumer['planningId'])) {
            $results['planning_id'] = (int) $paramsConsumer['planningId'];
        }

        return $results;
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * Poste une liste de ressource
     *
     * @param array $data Tableau de données à poster
     * @param AEntite $entite [Vide par définition]
     *
     * @return array Tableau d'id des créneaux nouvellement créés
     * @throws MissingArgumentException Si un élément requis n'est pas présent
     * @throws \DomainException Si un élément de la ressource n'est pas dans le bon domaine de définition
     */
    public function postList(array $data, AEntite $entite)
    {
        $postIds = [];
        $this->dao->beginTransaction();
        foreach ($data as $creneau) {
            try {
                $postIds[] = $this->postOne($creneau, $entite);
                /*
                 * Le plus cool aurait été de cloner l'objet de base,
                 * mais le clonage de mock est nul, donc on reset pour la boucle
                 */
                $entite->reset();
            } catch (\Exception $e) {
                $this->dao->rollback();
                throw $e;
            }
        }
        $this->dao->commit();

        return $postIds;
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * @inheritDoc
     */
    public function deleteOne(AEntite $entite)
    {
    }
}
