<?php
namespace Tests\Units\App\Libraries\Calendrier\Evenements;

use App\Libraries\Calendrier\Evenements\Ferie as _Ferie;

class Ferie extends \Tests\Units\TestUnit
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

    public function testGetListeVoid()
    {
        $this->calling($this->result)->fetch_all = [];
        $date = new \DateTimeImmutable();

        $ferie = new _Ferie($this->db);

        $this->array($ferie->getListe($date, $date))->isEmpty();
    }

    public function testGetListeFormatted()
    {
        $this->calling($this->result)->fetch_all = [
            ['jf_date' => '2017-02-12'],
            ['jf_date' => '23-04-2017']
        ];
        $date = new \DateTimeImmutable();

        $ferie = new _Ferie($this->db);

        $this->array($ferie->getListe($date, $date))->isIdenticalTo(['2017-02-12', '2017-04-23']);
    }
}
