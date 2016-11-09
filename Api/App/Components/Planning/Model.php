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

    /**
     * @inheritDoc
     */
    public function populate(array $data)
    {
        /*
         * chaque set a son propre domaine, et set erreur avec ses erreurs propres et ne fait pas le set
         * si erreur != vide, \DomainException avec les erreur en json
         */
    }

    // isPure() ? 
}
