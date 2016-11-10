<?php
namespace Api\App\Components\Planning\Creneau;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Api\Tests\Units\App\Components\Planning\Repository
 *
 * Ne devrait être contacté que par le Planning\Creneau\Controller, Planning\Repository
 * Ne devrait contacter que le Planning\Creneau\Model, Planning\Creneau\Dao
 */
class Repository extends \Api\App\Libraries\ARepository
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

        return new Model($this->getDataDao2Model($data));
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

        $models = [];
        foreach ($data as $value) {
            $model = new Model($this->getDataDao2Model($value));
            $models[$model->getId()] = $model;
        }

        return $models;
    }

    /**
     * Effectue le mapping des éléments venant de la DAO pour qu'ils soient compréhensibles pour le Modèle
     *
     * @param array $dataDao
     *
     * @return array
     */
    private function getDataDao2Model(array $dataDao)
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
     * Effectue le mapping des recherches du consommateur de l'API pour qu'elles
     * soient traitables par la DAO
     *
     * Essentiel pour séparer / traduire les contextes Client / DAO
     *
     * @param array $paramsConsumer Paramètres reçus
     *
     * @return array
     */
    private function getParamsConsumer2Dao(array $paramsConsumer)
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
     * @inheritDoc
     */
    public function postOne(array $data)
    {
    }
}
