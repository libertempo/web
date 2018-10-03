<?php declare(strict_types = 1);
namespace LibertAPI\Planning;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \LibertAPI\Tests\Units\Planning\PlanningRepository
 *
 * Ne devrait être contacté que par le PlanningController
 * Ne devrait contacter que le PlanningEntite
 */
class PlanningRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    final protected function getEntiteClass() : string
    {
        return PlanningEntite::class;
    }

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Storage(array $paramsConsumer) : array
    {
        unset($paramsConsumer);
        return [];
    }

    /**
     * @inheritDoc
     */
    final protected function getStorage2Entite(array $dataStorage)
    {
        return [
            'id' => $dataStorage['planning_id'],
            'name' => $dataStorage['name'],
            'status' => $dataStorage['status'],
        ];
    }

    /**
     * @inheritDoc
     */
    final protected function setValues(array $values)
    {
        $this->queryBuilder->setValue('name', ':name');
        $this->queryBuilder->setParameter(':name', $values['name']);
        $this->queryBuilder->setValue('status', $values['status']);
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2Storage(AEntite $entite) : array
    {
        return [
            'name' => $entite->getName(),
            'status' => $entite->getStatus(),
        ];
    }

    final protected function setSet(array $parametres)
    {
        if (!empty($parametres['name'])) {
            $this->queryBuilder->set('name', ':name');
            $this->queryBuilder->setParameter(':name', $parametres['name']);
        }
        if (!empty($parametres['status'])) {
            $this->queryBuilder->set('status', ':status');
            $this->queryBuilder->setParameter(':status', $parametres['status']);
        }
    }

    /**
     * Définit les filtres à appliquer à la requête
     *
     * @param array $parametres
     * @example [filter => []]
     */
    final protected function setWhere(array $parametres)
    {
        if (!empty($parametres['id'])) {
            $this->queryBuilder->andWhere('planning_id = :id');
            $this->queryBuilder->setParameter(':id', $parametres['id']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'planning';
    }
}
