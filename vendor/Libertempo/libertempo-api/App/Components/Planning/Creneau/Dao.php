<?php
namespace App\Components\Planning\Creneau;

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
class Dao extends \App\Libraries\ADao
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * @inheritDoc
     *
     * @param int $planningId Contrainte de recherche sur le planning
     */
    public function getById($id, $planningId = -1)
    {
        $req = 'SELECT * FROM ' . $this->getTableName();
        $filters = $this->getFilters(['id' => $id, 'planningId' => $planningId]);
        $req .= $filters['where'];
        $res = $this->storageConnector->prepare($req);
        $res->execute($filters['bind']);

        return $res->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres)
    {
        $req = 'SELECT * FROM ' . $this->getTableName();
        $filters = $this->getFilters($parametres);
        $req .= $filters['where'];
        $res = $this->storageConnector->prepare($req);
        $res->execute($filters['bind']);

        return $res->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Retourne le tableau des filtres à appliquer à la requête
     *
     * @param array $parametres
     *
     * @return array ['where' => clause complète, 'bind' => variables[]]
     */
    private function getFilters(array $parametres)
    {
        $where = [];
        $bind = [];
        if (!empty($parametres['id'])) {
            $where[] = 'creneau_id = :id';
            $bind[':id'] = $parametres['id'];
        }
        if (!empty($parametres['planning_id'])) {
            $where[] = 'planning_id = :planningId';
            $bind[':planningId'] = $parametres['planning_id'];
        }

        return [
            'where' => !empty($where)
                ? ' WHERE ' . implode(' AND ', $where)
                : '',
            'bind' => $bind,
        ];
    }

    /*************************************************
     * POST
     *************************************************/

    /**
     * @inheritDoc
     */
    public function post(array $data)
    {
        $req = 'INSERT INTO ' . $this->getTableName() . ' (planning_id, jour_id, type_semaine, type_periode, debut, fin)
            VALUES (:planning_id, :jour_id, :type_semaine, :type_periode, :debut, :fin)';
        $res = $this->storageConnector->prepare($req);
        $res->execute([
            ':planning_id' => $data['planning_id'],
            ':jour_id' => $data['jour_id'],
            ':type_periode' => $data['type_periode'],
            ':debut' => $data['debut'],
            ':fin' => $data['fin'],
        ]);

        return $this->storageConnector->lastInsertId();
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * @inheritDoc
     */
    public function put(array $data, $id)
    {
        $req = 'UPDATE ' . $this->getTableName() . '
            SET planning_id = :planning_id, jour_id = :jour_id, type_semaine = :type_semaine, type_periode = :type_periode, debut = :debut, fin = :fin
            WHERE id = :id';
        $res = $this->storageConnector->prepare($req);
        $res->execute([
            ':planning_id' => $data['planning_id'],
            ':jour_id' => $data['jour_id'],
            ':type_semaine' => $data['type_semaine'],
            ':type_periode' => $data['type_periode'],
            ':debut' => $data['debut'],
            ':fin' => $data['fin'],
            ':id' => $id,
        ]);

        return $res->rowCount();
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
