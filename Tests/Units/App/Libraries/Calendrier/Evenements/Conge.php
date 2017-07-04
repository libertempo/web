<?php
namespace Tests\Units\App\Libraries\Calendrier\Evenements;

use App\Libraries\Calendrier\Evenements\Conge as _Conge;

/**
 * Classe de test des collections de congés
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Conge extends \Tests\Units\TestUnit
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

        $conge = new _Conge($this->db);

        $this->array($conge->getListe($date, $date, [], false))->isEmpty();
    }

    public function testGetListeFilledSeveralDays()
    {
        $statut = \App\Models\Conge::STATUT_VALIDATION_FINALE;
        $this->calling($this->result)->fetch_all = [[
            'p_login' => 'Perceval Karadoc',
            'p_demi_jour_deb' => 'pm',
            'p_demi_jour_fin' => 'am',
            'p_etat' => $statut,
            'p_date_deb' => '2017-02-12',
            'p_date_fin' => '2017-02-15'
        ],];

        $conge = new _Conge($this->db);

        $nomComplet = 'Perceval Karadoc';
        $expected = [
            '2017-02-12' => [[
                'employe' => $nomComplet,
                'demiJournee' => 'pm',
                'statut' => $statut,
            ]],
            '2017-02-13' => [[
                'employe' => $nomComplet,
                'demiJournee' => '*',
                'statut' => $statut,
            ]],
            '2017-02-14' => [[
                'employe' => $nomComplet,
                'demiJournee' => '*',
                'statut' => $statut,
            ]],
            '2017-02-15' => [[
                'employe' => $nomComplet,
                'demiJournee' => 'am',
                'statut' => $statut,
            ]],
        ];
        $date = new \DateTimeImmutable();
        $liste = $conge->getListe($date, $date, ['Arthur'], false);
        ksort($liste);

        $this->array($liste)->isIdenticalTo($expected);
    }

    public function testGetListeFilledOneDay()
    {
        $statut = \App\Models\Conge::STATUT_VALIDATION_FINALE;
        $this->calling($this->result)->fetch_all = [[
            'p_login' => 'Perceval Karadoc',
            'p_demi_jour_deb' => 'am',
            'p_demi_jour_fin' => 'pm',
            'p_etat' => $statut,
            'p_date_deb' => '2017-02-12',
            'p_date_fin' => '2017-02-12'
        ],];

        $conge = new _Conge($this->db);

        $nomComplet = 'Perceval Karadoc';
        $expected = [
            '2017-02-12' => [[
                'employe' => $nomComplet,
                'demiJournee' => '*',
                'statut' => $statut,
            ]],
        ];
        $date = new \DateTimeImmutable();
        $liste = $conge->getListe($date, $date, ['Arthur'], false);
        ksort($liste);

        $this->array($liste)->isIdenticalTo($expected);
    }
}
