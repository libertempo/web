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
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     */
    public function __construct(\includes\SQL $db)
    {
        $this->db = $db;
    }

    /**
     * Retourne la collection d'événements relative à la période demandée
     *
     * @param \DateTimeInterface $dateDebut
     * @param \DateTimeInterface $dateFin
     *
     * @return \CalendR\Event\EventInterface[]
     */
    abstract public function getListe(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin);
}
