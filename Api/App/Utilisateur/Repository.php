<?php
namespace Api\App\Utilisateur;

/**
 * {@inheritDoc}
 *
 * Ne devrait être contacté que par le Planning\Controller
 * Ne devrait contacter que le Planning\Model, Planning\Dao */
class Repository extends \Api\App\Libraries\Repository
{
    public function getOne($id)
    {
        return [];
    }

    public function getList()
    {
        return [];
    }
}
