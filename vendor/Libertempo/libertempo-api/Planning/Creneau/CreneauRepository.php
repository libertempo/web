<?php declare(strict_types = 1);
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
    public function getOne(int $id, $planningId = -1) : AEntite
    {
        return $this->dao->getById($id, $planningId);
    }

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Dao(array $paramsConsumer) : array
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
    public function postList(array $data, AEntite $entite) : array
    {
        $postIds = [];
        $this->dao->beginTransaction();
        foreach ($data as $creneau) {
            try {
                $cloneEntite = clone $entite;
                $postIds[] = $this->postOne($creneau, $cloneEntite);
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
