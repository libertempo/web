<?php
namespace Api\App\Planning;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté que par Planning\Repository
 * Ne devrait contacter personne
 */
class Dao extends \Api\App\Libraries\Dao
{
    /**
     *
     */
    final protected function getTableName()
    {
        return 'planning';
    }

    /**
     * {@inheritDoc}
     */
    public function getById($id)
    {
        $req = $this->storageConnector->prepare(
            'SELECT *
            FROM ' . $this->getTableName() . '
            WHERE planning_id = :id'
        );
        $req->execute([
            ':id' => (int) $id,
        ]);

        return $req->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * {@inheritDoc}
     */
    public function getList(array $parametres)
    {
        return [];
    }
}
