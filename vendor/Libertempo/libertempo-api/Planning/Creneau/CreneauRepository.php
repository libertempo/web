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
        $id = (int) $id;
        $data = $this->dao->getById($id, $planningId);
        if (empty($data)) {
            throw new \DomainException('Creneau#' . $id . ' is not a valid resource');
        }

        return new CreneauEntite($this->getDataDao2Entite($data));
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres)
    {
        /* retourner une collection pour avoir le total, hors limite forcée (utile pour la pagination) */
        $data = $this->dao->getList($this->getParamsConsumer2Dao($parametres));
        if (empty($data)) {
            throw new \UnexpectedValueException('No resource match with these parameters');
        }

        $entites = [];
        foreach ($data as $value) {
            $entite = new CreneauEntite($this->getDataDao2Entite($value));
            $entites[$entite->getId()] = $entite;
        }

        return $entites;
    }

    /**
     * @inheritDoc
     */
    final protected function getDataDao2Entite(array $dataDao)
    {
        return [
            'id' => $dataDao['creneau_id'],
            'planningId' => $dataDao['planning_id'],
            'jourId' => $dataDao['jour_id'],
            'typeSemaine' => $dataDao['type_semaine'],
            'typePeriode' => $dataDao['type_periode'],
            'debut' => $dataDao['debut'],
            'fin' => $dataDao['fin'],
        ];
    }

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Dao(array $paramsConsumer)
    {
        $filterInt = function ($var) {
            return filter_var(
                $var,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );
        };
        $results = [];
        if (!empty($paramsConsumer['planningId'])) {
            $results['planning_id'] = $filterInt($paramsConsumer['planningId']);
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

    /**
     * @inheritDoc
     */
    public function postOne(array $data, AEntite $entite)
    {
        if (!$this->hasAllRequired($data)) {
            throw new MissingArgumentException('');
        }

        try {
            $entite->populate($data);
            $dataDao = $this->getEntite2DataDao($entite);

            return $this->dao->post($dataDao);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2DataDao(AEntite $entite)
    {
        return [
            'planning_id' => $entite->getPlanningId(),
            'jour_id' => $entite->getJourId(),
            'type_semaine' => $entite->getTypeSemaine(),
            'type_periode' => $entite->getTypePeriode(),
            'debut' => $entite->getDebut(),
            'fin' => $entite->getFin(),
        ];
    }

    /**
     * Vérifie que les données passées possèdent bien tous les champs requis
     *
     * @param array $data
     *
     * @return bool
     */
    private function hasAllRequired(array $data)
    {
        foreach ($this->getListRequired() as $value) {
            if (!isset($data[$value])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne la liste des champs requis
     *
     * @return array
     */
    private function getListRequired()
    {
        return ['planningId', 'jourId', 'typeSemaine', 'typePeriode', 'debut', 'fin'];
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * @inheritDoc
     */
    public function putOne(array $data, AEntite $entite)
    {
        if (!$this->hasAllRequired($data)) {
            throw new MissingArgumentException('');
        }

        try {
            $entite->populate($data);
            $dataDao = $this->getEntite2DataDao($entite);

            return $this->dao->put($dataDao, $entite->getId());
        } catch (\Exception $e) {
            throw $e;
        }
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
