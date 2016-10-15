<?php
namespace App\Libraries\Calendrier\Evenement;

use CalendR\Event\EventInterface;
use CalendR\Period\PeriodInterface;

/**
 * Événement commun
 *
 * Ne doit contacter personne
 * Ne doit être contacté que par \App\Libraries\Collection\*
 * Doit être immuable
 *
 * @see \Tests\Units\App\Libraries\Calendrier\Evenement\Commun
 */
final class Commun implements EventInterface
{
    /**
     * @var \DateTimeInterface Date de début
     */
    protected $debut;

    /**
     * @var \DateTimeInterface Date de fin
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
     * @var string
     */
    protected $name;

    /**
     * @var string Title Html
     */
    protected $title;

    /**
     * @param string $uid Identifiant unique au sein du calendrier
     * @param \DateTimeInterface $debut Date de début
     * @param \DateTimeInterface $fin Date de fin
     * @param string $name
     * @param string $title Title Html
     * @param string $class Classe html
     */
    public function __construct($uid, \DateTimeInterface $debut, \DateTimeInterface $fin, $name, $title, $class)
    {
        $this->uid = (string) $uid;
        $this->debut = clone $debut;
        $this->fin = clone $fin;
        $this->name = (string) $name;
        $this->title = (string) $title;
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
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return 'event ' . $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function contains(\DateTime $datetime)
    {
        return $datetime >= $this->getBegin() && $datetime < $this->getEnd();
    }

    /**
     * {@inheritDoc}
     */
    public function containsPeriod(PeriodInterface $period)
    {
        return $this->contains($period->getBegin()) && $this->contains($period->getEnd());
    }

    /**
     * {@inheritDoc}
     */
    public function isDuring(PeriodInterface $period)
    {
        return $this->getBegin() >= $period->getBegin() && $this->getEnd() < $period->getEnd();
    }
}
