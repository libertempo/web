<?php
namespace Tests\Units\App\Libraries\Calendrier\Evenement;

use App\Libraries\Calendrier\Evenement\Commun as _Commun;

class Commun extends \Tests\Units\TestUnit
{
    /**
     * @var string Date ISO de début de l'événement
     */
    private $debutEvenement = '2006-12-12';

    /**
     * @var string Date ISO de fin de l'événement
     */
    private $finEvenement = '2007-01-01';

    /**
     * @var _Commun
     */
    private $tested;

    /**
     * @var \CalendR\Calendar
     */
    private $calendar;

    /**
     * Bootstrap des tests
     */
    public function beforeTestMethod($method)
    {
        $uid = 'uniqid';
        $debut = new \DateTimeImmutable($this->debutEvenement);
        $fin = new \DateTimeImmutable($this->finEvenement);
        $name = 'John Malkovich';
        $title = 'titre';
        $class = 'class';
        $this->tested = new _Commun($uid, $debut, $fin, $name, $title, $class);
        $this->calendar = new \CalendR\Calendar();
    }

    /**
     * Vérifie le retour de l'attribut uid
     */
    public function testGetUid()
    {
        $this->string($this->tested->getUid())->isIdenticalTo('uniqid');
    }

    /**
     * Vérifie le retour de l'attribut begin
     */
    public function testGetBegin()
    {
        $this->string($this->tested->getBegin()->format('Y-m-d'))->isIdenticalTo($this->debutEvenement);
    }

    /**
     * Vérifie le retour de l'attribut end
     */
    public function testGetEnd()
    {
        $this->string($this->tested->getEnd()->format('Y-m-d'))->isIdenticalTo($this->finEvenement);
    }

    /**
     * Vérifie le retour de l'attribut name
     */
    public function testGetName()
    {
        $this->string($this->tested->getName())->isIdenticalTo('John Malkovich');
    }

    /**
     * Vérifie le retour de l'attribut title
     */
    public function testGetTitle()
    {
        $this->string($this->tested->getTitle())->isIdenticalTo('titre');
    }

    /**
     * Vérifie le retour de l'attribut class
     */
    public function testGetClass()
    {
        $this->string($this->tested->getClass())->isIdenticalTo('event class');
    }

    /**
     * Vérifie que l'événement ne contient pas une date passsée
     */
    public function testContainsBeforeBegin()
    {
        $this->boolean($this->tested->contains(new \DateTime('2006-12-10')))->isFalse();
    }

    /**
     * Vérifie que l'événement contient la date de début
     */
    public function testContainsEqualBegin()
    {
        $this->boolean($this->tested->contains(new \DateTime($this->debutEvenement)))->isTrue();
    }

    /**
     * Vérifie que l'événement contient une date intermédiaire
     */
    public function testContainsAfterBeginBeforeEnd()
    {
        $this->boolean($this->tested->contains(new \DateTime('2006-12-24')))->isTrue();
    }

    /**
     * Vérifie que l'événement ne contient pas la date de fin
     */
    public function testContainsEqualEnd()
    {
        $this->boolean($this->tested->contains(new \DateTime($this->finEvenement)))->isFalse();
    }

    /**
     * Vérifie que l'événement ne contient pas une date future
     */
    public function testContainsAfterEnd()
    {
        $this->boolean($this->tested->contains(new \DateTime('2007-01-02')))->isFalse();
    }

    /**
     * Vérifie que l'événement ne contient pas une période passée
     */
    public function testContainsPeriodBeforeBegin()
    {
        $date = new \DateTime('2006-12-08');
        $jour = $this->calendar->getDay($date);

        $this->boolean($this->tested->containsPeriod($jour))->isFalse();
    }

    /**
     * Vérifie que l'événement ne contient pas une période dont la fin est égale à celle de l'événement
     */
    public function testContainsPeriodEndEqualEventEnd()
    {
        $date = new \DateTime($this->finEvenement);
        $jour = $this->calendar->getDay($date);

        $this->boolean($this->tested->containsPeriod($jour))->isFalse();
    }

    /**
     * Vérifie que l'événement ne contient pas une période dont la fin est future
     */
    public function testContainsPeriodEndAfterEventEnd()
    {
        $date = new \DateTime('2007-01-06');
        $jour = $this->calendar->getDay($date);

        $this->boolean($this->tested->containsPeriod($jour))->isFalse();
    }

    /**
     * Vérifie que l'événement contient une période entre ses deux dates
     */
    public function testContainsPeriodInEvent()
    {
        $date = new \DateTime('2006-12-20');
        $jour = $this->calendar->getDay($date);

        $this->boolean($this->tested->containsPeriod($jour))->isTrue();
    }

    /**
     * Vérifie que l'événement n'est pas pendant une période, ayant une date de début dans le passé
     */
    public function testIsDuringBeforeBegin()
    {
        $date = new \DateTime('2007-01-01');
        $mois = $this->calendar->getMonth($date);

        $this->boolean($this->tested->isDuring($mois))->isFalse();
    }

    /**
     * Vérifie que l'événement n'est pas pendant une période, les deux ayant une date de fin identique
     */
    public function testIsDuringEqualEnd()
    {
        $date = new \DateTime('2006-12-01');
        $mois = $this->calendar->getMonth($date);

        $this->boolean($this->tested->isDuring($mois))->isFalse();
    }

    /**
     * Vérifie que l'événement n'est pas pendant une période, ayant une date de fin dans le future
     */
    public function testIsDuringAfterEnd()
    {
        $date = new \DateTime('2006-12-28');
        $semaine = $this->calendar->getWeek($date);

        $this->boolean($this->tested->isDuring($semaine))->isFalse();
    }

    /**
     * Vérifie que l'événement est pendant une période
     */
    public function testIsDuringInPeriod()
    {
        $debut = new \DateTimeImmutable($this->debutEvenement);
        $fin = new \DateTimeImmutable('2006-12-28');
        $tested = new _Commun('', $debut, $fin, '', '', '');
        $date = new \DateTime('2006-12-01');
        $mois = $this->calendar->getMonth($date);

        $this->boolean($tested->isDuring($mois))->isTrue();
    }
}
