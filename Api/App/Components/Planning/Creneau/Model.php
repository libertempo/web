<?php
namespace Api\App\Components\Planning\Creneau;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Api\Tests\Units\App\Components\Planning\Creneau\Model
 *
 * Ne devrait être contacté que par le Planning\Creneau\Repository
 * Ne devrait contacter personne
 */
class Model extends \Api\App\Libraries\Model
{
    public function getPlanningId()
    {
        return (int) $this->data['planningId'];
    }

    public function getJourId()
    {
        return (int) $this->data['jourId'];
    }

    public function getTypeSemaine()
    {
        return (int) $this->data['typeSemaine'];
    }

    public function getTypePeriode()
    {
        return (int) $this->data['typePeriode'];
    }

    public function getDebut()
    {
        return (int) $this->data['debut'];
    }

    public function getFin()
    {
        return (int) $this->data['fin'];
    }
}
