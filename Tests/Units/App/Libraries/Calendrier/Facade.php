<?php
namespace Tests\Units\App\Libraries\Calendrier;

use App\Libraries\Calendrier\Facade as _Facade;

/**
 * Classe de test du calendrier
 *
 * Rompt le principe du test unitaire, mais avec un Facade, c'est compliqué de faire autrement.
 * En cas d'idée pour isoler, hésitez pas.
 *
 * @since 1.10
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see App\Libraries\Calendrier\Facade
 */
class Facade extends \Tests\Units\TestUnit
{
    private $dateDebut;
    private $dateFin;
    private $employes = ['Babar', 'Rintintin'];
    private $injectableCreator;
    private $weekend;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->dateDebut = new \DateTimeImmutable('2017-02-01');
        $this->dateFin = new \DateTimeImmutable('2017-02-28');
        $this->mockGenerator->orphanize('__construct');
        $this->injectableCreator = new \mock\App\Libraries\InjectableCreator();
        $this->mockGenerator->orphanize('__construct');
        $this->weekend = new \mock\App\Libraries\Calendrier\Collection\Weekend();
        $this->calling($this->injectableCreator)->get = $this->weekend;
    }

    public function testGetEmployes()
    {
        $this->calling($this->weekend)->getListe = [];
        $calendrier = new _Facade($this->injectableCreator, $this->employes, $this->dateDebut, $this->dateFin);

        $this->array($calendrier->getEmployes())->isIdenticalTo($this->employes);
    }

    public function testGetEvenementsDateEmployeInconnu()
    {
        $this->calling($this->weekend)->getListe = [];
        $calendrier = new _Facade($this->injectableCreator, $this->employes, $this->dateDebut, $this->dateFin);

        $this->exception(function () use ($calendrier) {
            $calendrier->getEvenementsDate('PetitLapin', '0000-00-00');
        })->isInstanceOf('\DomainException');
    }

    public function testGetEvenementsDateDateInconnue()
    {
        $this->calling($this->weekend)->getListe = ['2017-02-12'];
        $this->calling($this->weekend)->getListe[4] = [];
        $this->calling($this->weekend)->getListe[5] = [];
        $calendrier = new _Facade($this->injectableCreator, $this->employes, $this->dateDebut, $this->dateFin);

        $this->array($calendrier->getEvenementsDate('Babar', '2017-02-10'))
            ->isIdenticalTo([]);
    }

    /**
     * Test de l'absorption des autres événements quand il y a un weekend
     */
    public function testGetEvenenementsDateWeekend()
    {
        // définition d'un autre événement avec weekend
        $this->calling($this->weekend)->getListe = ['2017-02-12'];
        $this->calling($this->weekend)->getListe[4] = [];
        $this->calling($this->weekend)->getListe[5] = [];
        $calendrier = new _Facade($this->injectableCreator, $this->employes, $this->dateDebut, $this->dateFin);

        $this->array($calendrier->getEvenementsDate('Babar', '2017-02-12'))
            ->isIdenticalTo(['weekend']);
    }

    // public function testGetEvenenementsDatePlusieurs()
    // du coup le "plusieurs", pas avec weekend
    // pareil pour title, mais à ce stade, je ne sais pas encore ce que je veux

}
