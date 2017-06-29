<?php
namespace App\Components\Planning;

use App\Exceptions\MissingArgumentException;
use App\Libraries\AModel;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Tests\Units\App\Components\Planning\Repository
 *
 * Ne devrait être contacté que par le Planning\Controller
 * Ne devrait contacter que le Planning\Model, Planning\Dao
 */
class Repository extends \App\Libraries\ARepository
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * @inheritDoc
     */
    public function getOne($id)
    {
        $id = (int) $id;
        $data = $this->dao->getById($id);
        if (empty($data)) {
            throw new \DomainException('Planning#' . $id . ' is not a valid resource');
        }

        return new Model($this->getDataDao2Model($data));
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres)
    {
        /* TODO: retourner une collection pour avoir le total, hors limite forcée (utile pour la pagination) */
        /*
        several params :
        offset (first, !isset => 0) / start-after ?
        Limit (nb elements)
        filter (dimensions forced)
        */
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
            'id' => $dataDao['planning_id'],
            'name' => $dataDao['name'],
            'status' => $dataDao['status'],
        ];
    }

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
        if (!empty($paramsConsumer['limit'])) {
            $results['limit'] = $filterInt($paramsConsumer['limit']);
        }
        if (!empty($paramsConsumer['start-after'])) {
            $results['lt'] = $filterInt($paramsConsumer['start-after']);

        }
        if (!empty($paramsConsumer['start-before'])) {
            $results['gt'] = $filterInt($paramsConsumer['start-before']);
        }
        return $results;
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * @inheritDoc
     */
    public function postOne(array $data, AModel $model)
    {
        if (!$this->hasAllRequired($data)) {
            throw new MissingArgumentException('');
        }

        try {
            $model->populate($data);
            $dataDao = $this->getModel2DataDao($model);

            return $this->dao->post($dataDao);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Effectue le mapping des éléments venant du modèle pour qu'ils soient compréhensibles pour la DAO
     *
     * @param Model $model
     *
     * @return array
     */
    private function getModel2DataDao(AModel $model)
    {
        return [
            'name' => $model->getName(),
            'status' => $model->getStatus(),
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
        return ['name', 'status'];
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * @inheritDoc
     */
    public function putOne(array $data, AModel $model)
    {
        if (!$this->hasAllRequired($data)) {
            throw new MissingArgumentException('');
        }

        try {
            $model->populate($data);
            $dataDao = $this->getModel2DataDao($model);

            return $this->dao->put($dataDao, $model->getId());
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
    public function deleteOne(AModel $model)
    {
        try {
            $model->reset();
            $this->dao->delete($model->getId());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
