<?php
namespace Tests\Units\App\Libraries\Calendrier;

use App\Libraries\Calendrier\Evenements as _Evenements;

/**
 * Classe de test du calendrier
 *
 * @since 1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see App\Libraries\Calendrier\Evenements
 */
class Evenements extends \Tests\Units\TestUnit
{
    private $dateDebut;
    private $dateFin;
    private $employes = ['Babar', 'Rintintin'];
    private $injectableCreator;
    private $evenement;

    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->dateDebut = new \DateTimeImmutable('2017-02-01');
        $this->dateFin = new \DateTimeImmutable('2017-02-28');
        $this->mockGenerator->orphanize('__construct');
        $this->injectableCreator = new \mock\App\Libraries\InjectableCreator();
        $this->mockGenerator->orphanize('__construct');
        $this->evenement = new \mock\App\Libraries\Calendrier\Evenements\Weekend();
        $this->calling($this->injectableCreator)->get = $this->evenement;
    }

    public function testGetEvenementsDateDateInconnue()
    {
        $this->calling($this->evenement)->getListe = ['2017-02-12'];
        $this->calling($this->evenement)->getListe[4] = [];
        $this->calling($this->evenement)->getListe[5] = [];
        $this->calling($this->evenement)->getListe[6] = [];
        $calendrier = new _Evenements($this->injectableCreator);
        $calendrier->fetchEvenements($this->dateDebut, $this->dateFin, $this->employes, false, false);

        $this->array($calendrier->getEvenementsDate('Babar', '2017-02-10'))
            ->isIdenticalTo([]);
    }

    /**
     * Test de l'absorption des autres événements quand il y a un weekend
     */
    public function testGetEvenenementsDateWeekend()
    {
        // définition d'un autre événement avec weekend
        $this->calling($this->evenement)->getListe = ['2017-02-12'];
        $this->calling($this->evenement)->getListe[4] = [];
        $this->calling($this->evenement)->getListe[5] = [];
        $this->calling($this->evenement)->getListe[6] = [];
        $calendrier = new _Evenements($this->injectableCreator);
        $calendrier->fetchEvenements($this->dateDebut, $this->dateFin, $this->employes, false, false);

        $this->array($calendrier->getEvenementsDate('Babar', '2017-02-12'))
            ->isIdenticalTo(['weekend']);
    }
}
