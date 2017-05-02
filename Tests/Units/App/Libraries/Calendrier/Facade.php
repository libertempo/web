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
class Facade  extends \Tests\Units\TestUnit
{
    public function beforeTestMethod($method)
    {
        $this->dateDebut = new \DateTimeImmutable('2017-02-01');
        $this->dateFin = new \DateTimeImmutable('2017-02-28');
        $this->result = new \mock\MYSQLIResult();
        $this->db = new \mock\includes\SQL();
        $this->calling($this->db)->query = $this->result;
    }

    private $dateDebut;
    private $dateFin;
    private $employes = ['Babar', 'Rintintin'];
    private $result;
    private $db;

    public function testGetEvenementsDateEmployeInconnu()
    {
        $this->getEvenementsExistenceKo();
    }

    public function testGetEvenementsDateDateInconnue()
    {
        $this->getEvenementsExistenceKo();
    }

    private function getEvenementsDateExistenceKo()
    {
        $this->calling($this->result)->fetch_assoc = ['conf_valeur' => 'TRUE'];
        $calendrier = new _Facade($this->employes, $this->db, $this->dateDebut, $this->dateFin);

        $this->exception(function () use ($calendrier) {
            $calendrier->getEvenementsDate('PetitLapin', '0000-00-00');
        })->isInstanceOf('\DomainException');
    }

    /**
     * provider
     */
    public function testGetEvenementsDateWeekend()
    {
        $this->calling($this->result)->fetch_assoc = ['conf_valeur' => false];
        $calendrier = new _Facade($this->employes, $this->db, $this->dateDebut, $this->dateFin);

        $this->array($calendrier->getEvenementsDate('Babar', '2017-02-05'))->isIdenticalTo(['weekend']);
    }
}
