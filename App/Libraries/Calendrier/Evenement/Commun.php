<?php
namespace App\Libraries\Calendrier\Evenement;

use IIdentifiable;
use CalendR\Event\AbstractEvent;

/**
 * Événement commun
 *
 * Ne doit contacter personne
 * Ne doit être contacté que par \App\Libraries\Collection\Ferie
 */
class Commun extends AbstractEvent implements IIdentifiable
{
    /**
     * @var \DateTime Date de début
     */
    protected $debut;

    /**
     * @var \DateTime Date de fin
     */
    protected $fin;

    /**
     * @var string Identifiant unique au sein du calendrier
     */
    protected $uid;

    /**
     * @var string Classe html
     */
    protected $class;

    /**
     * @param string Identifiant unique au sein du calendrier
     * @param \DateTime Date de début
     * @param \DateTime Date de fin
     * @param string Classe html
     */
    public function __construct($uid, \DateTime $debut, \DateTime $fin, $class)
    {
        $this->uid = (string) $uid;
        $this->debut = clone $debut;
        $this->fin = clone $fin;
        $this->class = (string) $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * {@inheritDoc}
     */
    public function getBegin()
    {
        return $this->debut;
    }

    /**
     * {@inheritDoc}
     */
    public function getEnd()
    {
        return $this->fin;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return null;
    }
}
