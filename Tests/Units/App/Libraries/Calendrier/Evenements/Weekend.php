<?php
namespace Tests\Units\App\Libraries\Calendrier\Evenements;

use App\Libraries\Calendrier\Evenements\Weekend as _Weekend;

class Weekend extends \Tests\Units\TestUnit
{
    public function beforeTestMethod($method)
    {
        parent::beforeTestMethod($method);
        $this->result = new \mock\MYSQLIResult();
        $this->db = new \mock\includes\SQL();
        $this->calling($this->db)->query = $this->result;
    }

    private $db;
    private $result;

    public function testGetListeWeekendTravaille()
    {
        $this->calling($this->result)->fetch_assoc = ['conf_valeur' => 'TRUE'];
        $date = new \DateTimeImmutable();

        $weekend = new _Weekend($this->db);

        $this->array($weekend->getListe($date, $date))->isEmpty();
    }

    public function testGetListeSamediNonTravailleSeulement()
    {
        $this->calling($this->result)->fetch_assoc[1] = ['conf_valeur' => false];
        $this->calling($this->result)->fetch_assoc[2] = ['conf_valeur' => 'TRUE'];
        $debut = new \DateTimeImmutable('2017-02-01');
        $fin = new \DateTimeImmutable('2017-02-28');

        $weekend = new _Weekend($this->db);

        $expected = [
            '2017-02-04',
            '2017-02-11',
            '2017-02-18',
            '2017-02-25'
        ];

        $this->array($weekend->getListe($debut, $fin))->isIdenticalTo($expected);
    }

    public function testGetListeDimancheNonTravailleSeulement()
    {
        $this->calling($this->result)->fetch_assoc[1] = ['conf_valeur' => 'TRUE'];
        $this->calling($this->result)->fetch_assoc[2] = ['conf_valeur' => false];
        $debut = new \DateTimeImmutable('2017-02-01');
        $fin = new \DateTimeImmutable('2017-02-28');

        $weekend = new _Weekend($this->db);

        $expected = [
            '2017-02-05',
            '2017-02-12',
            '2017-02-19',
            '2017-02-26'
        ];

        $this->array($weekend->getListe($debut, $fin))->isIdenticalTo($expected);
    }
}
