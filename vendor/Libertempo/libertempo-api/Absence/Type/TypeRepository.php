<?php declare(strict_types = 1);
namespace LibertAPI\Absence\Type;

use LibertAPI\Tools\Libraries\AEntite;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 * @see \Tests\Units\Absence\Type\TypeRepository
 */
class TypeRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    final protected function getEntiteClass() : string
    {
        return TypeEntite::class;
    }

    /**
     * {@inheritDoc}
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
            'id' => $dataStorage['ta_id'],
            'type' => $dataStorage['ta_type'],
            'libelle' => $dataStorage['ta_libelle'],
            'libelleCourt' => $dataStorage['ta_short_libelle'],
        ];
    }

    /**
     * Définit les values à insérer
     *
     * @param array $values
     */
    final protected function setValues(array $values)
    {
        $this->queryBuilder->setValue('ta_type', ':type');
        $this->queryBuilder->setParameter(':type', $values['type']);
        $this->queryBuilder->setValue('ta_libelle', ':libelle');
        $this->queryBuilder->setParameter(':libelle', $values['libelle']);
        $this->queryBuilder->setValue('ta_short_libelle', ':libelleCourt');
        $this->queryBuilder->setParameter(':libelleCourt', $values['libelleCourt']);
    }

    final protected function setSet(array $parametres)
    {
        if (!empty($parametres['type'])) {
            $this->queryBuilder->set('ta_type', ':type');
            $this->queryBuilder->setParameter(':type', $parametres['type']);
        }
        if (!empty($parametres['libelle'])) {
            $this->queryBuilder->set('ta_libelle', ':libelle');
            // @TODO : changer le schema
            $this->queryBuilder->setParameter(':libelle', $parametres['libelle']);
        }
        if (!empty($parametres['libelleCourt'])) {
            $this->queryBuilder->set('ta_short_libelle', ':libelleCourt');
            $this->queryBuilder->setParameter(':libelleCourt', $parametres['libelleCourt']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getEntite2Storage(AEntite $entite) : array
    {
        return [
            'type' => $entite->getType(),
            'libelle' => $entite->getLibelle(),
            'libelleCourt' => $entite->getLibelleCourt(),
        ];
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
            $this->queryBuilder->andWhere('ta_id = :id');
            $this->queryBuilder->setParameter(':id', (int) $parametres['id']);
        }
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName() : string
    {
        return 'conges_type_absence';
    }
}
