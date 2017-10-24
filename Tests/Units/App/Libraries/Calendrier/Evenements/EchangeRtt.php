<?php
namespace Tests\Units\App\Libraries\Calendrier\Evenements;

use App\Libraries\Calendrier\Evenements\EchangeRtt as _EchangeRtt;

/**
 * Classe de test des échanges RTT
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class EchangeRtt extends \Tests\Units\TestUnit
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

        $echangeRtt = new _EchangeRtt($this->db);

        $this->array($echangeRtt->getListe($date, $date, []))->isEmpty();
    }

    public function testGetListeFilled()
    {
        $this->calling($this->result)->fetch_all = [[
            'e_login' => 'Batman',
            'e_absence' => 'J',
            'e_date_jour' => '2017-02-03',
        ],];

        $echange = new _EchangeRtt($this->db);

        $expected = [
            '2017-02-03' => [[
                'employe' => 'Batman',
                'demiJournee' => '*',
            ]],
        ];
        $date = new \DateTimeImmutable();
        $liste = $echange->getListe($date, $date, ['Batman']);
        ksort($liste);

        $this->array($liste)->isIdenticalTo($expected);
    }
}
