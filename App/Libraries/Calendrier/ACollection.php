<?php
namespace App\Libraries\Calendrier;

/**
 * Collection d'événements
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * Ne doit contacter personne
 * Ne doit être contacté que par \App\Libraries\Calendrier\Collection/*
 */
abstract class ACollection
{
    /**
     * @var \DateTime
     */
    protected $dateDebut;

    /**
     * @var \DateTime
     */
    protected $dateFin;

    /**
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     */
    public function __construct(\DateTime $dateDebut, \DateTime $dateFin)
    {
        $this->dateDebut = clone $dateDebut;
        $this->dateFin = clone $dateFin;
    }

    /**
     * Retourne la collection d'événements
     *
     * @return \CalendR\Event\EventInterface[]
     */
    abstract public function getListe();
}
