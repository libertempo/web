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
     * @var \includes\SQL Objet de DB
     */
    protected $db;

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
    public function __construct(\includes\SQL $db, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin)
    {
        $this->db = $db;
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
