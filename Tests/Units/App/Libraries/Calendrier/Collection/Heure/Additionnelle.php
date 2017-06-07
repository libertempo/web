<?php
namespace Tests\Units\App\Libraries\Calendrier\Collection\Heure;

use App\Libraries\Calendrier\Collection\Heure\Additionnelle as _Additionnelle;

/**
 * Classe de test des collections d'heures additionnelles
 */
class Additionnelle extends \Tests\Units\TestUnit
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

        $heures = new _Additionnelle($this->db, []);

        $this->array($heures->getListe($date, $date, [], false))->isEmpty();
    }

    public function testGetListeFilled()
    {
        $this->calling($this->result)->fetch_all[1] = [['id' => 3]];
        $this->calling($this->result)->fetch_all[2] = [[
            'login' => 'Provencal le Gaulois',
            'debut' => 1191929182,
            'fin' =>   1199128919,
            'statut' => \App\Models\AHeure::STATUT_VALIDATION_FINALE,
        ],];

        $heures = new _Additionnelle($this->db);

        $nomComplet = 'Provencal le Gaulois';
        $expected = [
            '2007-10-09' => [[
                'employe' => $nomComplet,
                'statut' => \App\Models\AHeure::STATUT_VALIDATION_FINALE,
            ]],
        ];
        $date = new \DateTimeImmutable();
        $liste = $heures->getListe($date, $date, [], false);

        $this->array($liste)->isIdenticalTo($expected);
    }
}
