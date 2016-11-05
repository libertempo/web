<?php
namespace Api\App\Components\Planning\Creneau;

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
class Dao extends \Api\App\Libraries\Dao
{
    /**
     * @inheritDoc
     */
    public function getById($id)
    {
    }

    /**
     * @inheritDoc
     */
    public function getList(array $parametres)
    {
    }

    /**
     * @inheritDoc
     */
    final protected function getTableName()
    {
    }
}
