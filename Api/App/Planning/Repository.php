<?php
namespace Api\App\Planning;

/**
 * {@inheritDoc}
 *
 * Ne devrait être contacté que par le Planning\Controller
 * Ne devrait contacter que le Planning\Model, Planning\Dao
 */
class Repository extends \Api\App\Libraries\Repository
{
    /**
     * Retourne une ressource unique
     *
     * @return Model
     */
    public function getOne($id)
    {
        $id = (int) $id;

        return ['a'];
    }

    /**
     *
     */
    public function getList()
    {
        /*
         several params :
            offset (first, !isset => 0) / start-after ?
            Limit (nb elements)
            filter (dimensions forced)
          */
        return [];
    }
}
