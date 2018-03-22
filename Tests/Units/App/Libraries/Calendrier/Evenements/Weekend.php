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

        $this->config = new \mock\App\Libraries\Configuration($this->db);

    }

    private $db;
    private $result;
    private $config;

    public function testGetListeWeekendTravaille()
    {
        $this->calling($this->config)->isSamediOuvrable = true;
        $this->calling($this->config)->isDimancheOuvrable = true;
        $date = new \DateTimeImmutable();

        $weekend = new _Weekend($this->config);

        $this->array($weekend->getListe($date, $date))->isEmpty();
    }

    public function testGetListeSamediNonTravailleSeulement()
    {
        $this->calling($this->config)->isSamediOuvrable = false;
        $this->calling($this->config)->isDimancheOuvrable = true;

        $debut = new \DateTimeImmutable('2017-02-01');
        $fin = new \DateTimeImmutable('2017-02-28');

        $weekend = new _Weekend($this->config);

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
        $this->calling($this->config)->isSamediOuvrable = true;
        $this->calling($this->config)->isDimancheOuvrable = false;
        $debut = new \DateTimeImmutable('2017-02-01');
        $fin = new \DateTimeImmutable('2017-02-28');

        $weekend = new _Weekend($this->config);

        $expected = [
            '2017-02-05',
            '2017-02-12',
            '2017-02-19',
            '2017-02-26'
        ];

        $this->array($weekend->getListe($debut, $fin))->isIdenticalTo($expected);
    }
}
