<?php
namespace Api\App\Components\Planning;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Api\Tests\Units\App\Components\Planning\Model
 *
 * Ne devrait être contacté que par le Planning\Repository
 * Ne devrait contacter personne
 */
class Model extends \Api\App\Libraries\Model
{
    public function getName()
    {
        return $this->data['name'];
    }

    public function getStatus()
    {
        return (int) $this->data['status'];
    }
}
