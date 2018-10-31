<?php declare(strict_types = 1);
namespace LibertAPI\Groupe;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.7
 * @see \LibertAPI\Tests\Units\Planning\PlanningRepository
 *
 * Ne devrait être contacté que par le GroupeController
 * Ne devrait contacter que le GroupeEntite
 */
class GroupeRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    final protected function getEntiteClass() : string
    {
        return GroupeEntite::class;
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
            'id' => $dataStorage['g_gid'],
            'name' => $dataStorage['g_groupename'],
            'comment' => $dataStorage['g_comment'],
            'double_validation' => 'Y' === $dataStorage['g_double_valid']
        ];
    }

    /**
     * @inheritDoc
     */
    final protected function setValues(array $values)
    {
        $this->queryBuilder->setValue('g_groupename', ':name');
        $this->queryBuilder->setParameter(':name', $values['name']);
        $this->queryBuilder->setValue('g_comment', $values['comment']);
        $this->queryBuilder->setValue('g_double_valid', $values['double_validation']);
    }

    final protected function setSet(array $parametres)
    {
        if (!empty($parametres['name'])) {
            $this->queryBuilder->set('g_groupename', ':name');
            $this->queryBuilder->setParameter(':name', $parametres['name']);
        }
        if (!empty($parametres['comment'])) {
            $this->queryBuilder->set('g_comment', ':comment');
            $this->queryBuilder->setParameter(':comment', $parametres['comment']);
        }
        if (!empty($parametres['double_validation'])) {
            $this->queryBuilder->set('g_double_valid', ':double_validation');
            $this->queryBuilder->setParameter(':double_validation', $parametres['double_validation']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function setWhere(array $parametres)
    {
        if (array_key_exists('id', $parametres)) {
            $this->queryBuilder->andWhere('g_gid = :id');
            $this->queryBuilder->setParameter(':id', (int) $parametres['id']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2Storage(AEntite $entite) : array
    {
        return [
            'name' => $entite->getName(),
            'comment' => $entite->getComment(),
            'double_validation' => 'Y' === $entite->isDoubleValidated()
        ];
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_groupe';
    }
}
