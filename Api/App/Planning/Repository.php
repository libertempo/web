<?php
namespace Api\App\Planning;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Api\Tests\Units\App\Planning\Repository
 *
 * Ne devrait être contacté que par le Planning\Controller
 * Ne devrait contacter que le Planning\Model, Planning\Dao
 *
 * mettre une option "with-dependencies" pour avoir ou non les dépendances dans le json résultant
 */
class Repository extends \Api\App\Libraries\Repository
{
    /**
     * Retourne une ressource unique
     *
     * @param int $id Id potentiel de planning
     *
     * @return Model
     * @throws \DomainException Si $id n'est pas dans le domaine de définition
     */
    public function getOne($id)
    {
        $id = (int) $id;
        $data = $this->dao->getById($id);
        if (empty($data)) {
            throw new \DomainException('Planning#' . $id . ' is not a valid resource');
        }

        $modelData = $this->getDataDao2Model($data);
        $modelId = $modelData['id'];
        unset($modelData['id']);

        return new Model($modelId, $modelData);
    }

    /**
     * Retourne une liste de ressource correspondant à des critères
     *
     * @param array $parametres
     * @example [offset => 4, start-after => 23, filter => 'name::chapo|status::1,3']
     *
     * @return array [$objetId => $objet]
     * @throws \UnexpectedValueException Si les critères ne sont pas pertinents
     */
    public function getList(array $parametres)
    {
        /* retourner une collection pour avoir le total, hors limite forcée (utile pour la pagination) */
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
            $modelData = $this->getDataDao2Model($value);
            $modelId = $modelData['id'];
            unset($modelData['id']);
            $model = new Model($modelId, $modelData);
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
        return [
            'limit' => $filterInt($paramsConsumer['limit']),
            'lt' => $filterInt($paramsConsumer['start-after']),
            'gt' => $filterInt($paramsConsumer['start-before']),
        ];
    }
}
