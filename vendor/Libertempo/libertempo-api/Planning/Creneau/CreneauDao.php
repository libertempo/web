<?php
namespace LibertAPI\Planning\Creneau;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté que par Planning\Creneau\Repository
 * Ne devrait contacter personne
 */
class CreneauDao extends \LibertAPI\Tools\Libraries\ADao
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * @inheritDoc
     *
     * @param int $planningId Contrainte de recherche sur le planning
     */
    public function getById($id, $planningId = null)
    {
        $this->queryBuilder->select('*');
        $this->setWhere(['id' => $id, 'planning_id' => $planningId]);
        $res = $this->queryBuilder->execute();

        return $res->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres)
    {
        $this->queryBuilder->select('*');
        $this->setWhere($parametres);
        $res = $this->queryBuilder->execute();

        return $res->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Définit les filtres à appliquer à la requête
     *
     * @param array $parametres
     */
    private function setWhere(array $parametres)
    {
        if (!empty($parametres['id'])) {
            $this->queryBuilder->andWhere('creneau_id = :id');
            $this->queryBuilder->setParameter(':id', (int) $parametres['id']);
        }
        if (!empty($parametres['planning_id'])) {
            $this->queryBuilder->andWhere('planning_id = :planningId');
            $this->queryBuilder->setParameter(':planningId', (int) $parametres['planning_id']);
        }
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * @inheritDoc
     */
    public function post(array $data)
    {
        $this->queryBuilder->insert($this->getTableName());
        $this->setValues($data);
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
        $this->queryBuilder->setValue('planning_id', (int) $values['planning_id']);
        $this->queryBuilder->setValue('jour_id', (int) $values['jour_id']);
        $this->queryBuilder->setValue('type_semaine', $values['type_semaine']);
        $this->queryBuilder->setValue('type_periode', $values['type_periode']);
        $this->queryBuilder->setValue('debut', $values['debut']);
        $this->queryBuilder->setValue('fin', $values['fin']);
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * @inheritDoc
     */
    public function put(array $data, $id)
    {
        $this->queryBuilder->update($this->getTableName());
        $this->queryBuilder->where('creneau_id = :id');
        $this->queryBuilder->setParameter(':id', (int) $id);
        $this->setSet($data);

        $this->queryBuilder->execute();
    }

    private function setSet(array $parametres)
    {
        if (!empty($parametres['planning_id'])) {
            $this->queryBuilder->set('planning_id', ':planning_id');
            $this->queryBuilder->setParameter(':planning_id', $parametres['planning_id']);
        }
        if (!empty($parametres['jour_id'])) {
            $this->queryBuilder->set('jour_id', ':jour_id');
            $this->queryBuilder->setParameter(':jour_id', (int) $parametres['jour_id']);
        }
        if (!empty($parametres['type_semaine'])) {
            $this->queryBuilder->set('type_semaine', ':type_semaine');
            $this->queryBuilder->setParameter(':type_semaine', $parametres['type_semaine']);
        }
        if (!empty($parametres['type_periode'])) {
            $this->queryBuilder->set('type_periode', ':type_periode');
            $this->queryBuilder->setParameter(':type_periode', $parametres['type_periode']);
        }
        if (!empty($parametres['debut'])) {
            $this->queryBuilder->set('debut', ':debut');
            $this->queryBuilder->setParameter(':debut', $parametres['debut']);
        }
        if (!empty($parametres['fin'])) {
            $this->queryBuilder->set('fin', ':fin');
            $this->queryBuilder->setParameter(':fin', $parametres['fin']);
        }
    }

    /*************************************************
     * DELETE
     *************************************************/

    /**
     * @inheritDoc
     */
    public function delete($id)
    {
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName()
    {
        return 'planning_creneau';
    }
}
