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
 */
class TypeDao extends \LibertAPI\Tools\Libraries\ADao
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * @inheritDoc
     */
    public function getById(int $id) : AEntite
    {
        $this->queryBuilder->select('*');
        $this->setWhere(['id' => $id]);
        $res = $this->queryBuilder->execute();

        $data = $res->fetch(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            throw new \DomainException('#' . $id . ' is not a valid resource');
        }

        return new TypeEntite($this->getStorage2Entite($data));
    }

    /**
     * @inheritDoc
     */
    final protected function getStorage2Entite(array $dataDao)
    {
        return [
            'id' => $dataDao['ta_id'],
            'type' => $dataDao['ta_type'],
            'libelle' => $dataDao['ta_libelle'],
            'libelleCourt' => $dataDao['ta_short_libelle'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres) : array
    {
        $this->queryBuilder->select('*');
        $this->setWhere($parametres);
        $res = $this->queryBuilder->execute();

        $data = $res->fetchAll(\PDO::FETCH_ASSOC);
        if (empty($data)) {
            throw new \UnexpectedValueException('No resource match with these parameters');
        }

        $entites = array_map(function ($value) {
                return new TypeEntite($this->getStorage2Entite($value));
            },
            $data
        );

        return $entites;
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * @inheritDoc
     */
    public function post(AEntite $entite) : int
    {
        $this->queryBuilder->insert($this->getTableName());
        $this->setValues($this->getEntite2Storage($entite));
        $this->queryBuilder->execute();

        return $this->storageConnector->lastInsertId();
    }

    /**
     * Définit les values à insérer
     *
     * @param array $values
     */
    private function setValues(array $values)
    {
        $this->queryBuilder->setValue('ta_type', ':type');
        $this->queryBuilder->setParameter(':type', $values['type']);
        $this->queryBuilder->setValue('ta_libelle', ':libelle');
        $this->queryBuilder->setParameter(':libelle', $values['libelle']);
        $this->queryBuilder->setValue('ta_short_libelle', ':libelleCourt');
        $this->queryBuilder->setParameter(':libelleCourt', $values['libelleCourt']);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * @inheritDoc
     */
    public function put(AEntite $entite)
    {
        $this->queryBuilder->update($this->getTableName());
        $this->setSet($this->getEntite2Storage($entite));
        $this->queryBuilder->where('ta_id = :id');
        $this->queryBuilder->setParameter(':id', $entite->getId());

        $this->queryBuilder->execute();
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

    private function setSet(array $parametres)
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

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * @inheritDoc
     */
    public function delete(int $id) : int
    {
        $this->queryBuilder->delete($this->getTableName());
        $this->setWhere(['ta_id' => $id]);
        $res = $this->queryBuilder->execute();

        return $res->rowCount();
    }

    /**
     * Définit les filtres à appliquer à la requête
     *
     * @param array $parametres
     * @example [filter => []]
     */
    private function setWhere(array $parametres)
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
