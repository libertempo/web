<?php
namespace App\Libraries\Calendrier;

use \CalendR\Event\EventInterface;
use \CalendR\Event\Provider\ProviderInterface;
use \App\Libraries\Calendrier\BusinessCollection;

/**
 * Fournisseur d'événements pour le calendrier
 *
 * Ne doit contacter personne
 * Ne doit être contacté que par \CalendR\Calendar et \App\ProtoControllers\Calendrier
 *
 * @TODO benchmark sur la taille de collection
 */
class Fournisseur implements ProviderInterface, \IteratorAggregate, \Countable
{
    /**
     * @var EventInterface[]
     */
    private $evenements = [];

    /**
     * Construit le fournisseur en récupérant tous les événements de l'appli
     */
    public function __construct(BusinessCollection $collection)
    {
        $this->evenements = $collection->getListe();
    }

    /**
     * {@inheritDoc}
     */
    public function getEvents(\DateTime $begin, \DateTime $end, array $options = [])
    {
        return $this->evenements;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->evenements);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return count($this->evenements);
    }
}
