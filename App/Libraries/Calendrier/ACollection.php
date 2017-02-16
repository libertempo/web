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
     * @var \DateTimeInterface
     */
    protected $dateDebut;

    /**
     * @var \DateTimeInterface
     */
    protected $dateFin;

    /**
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     */
    public function __construct(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
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
