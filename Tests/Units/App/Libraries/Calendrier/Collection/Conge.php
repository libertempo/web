<?php
namespace Tests\Units\App\Libraries\Calendrier\Collection;

use App\Libraries\Calendrier\Collection\Conge as _Conge;

/**
 * Classe de test des collections de congés
 *
 * Réfléchir à l'immuabilité de Conges et au défaut de design que ça pose de le faire sauter pour tirer parti de l'injectableCreator
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

        $cogne = new _Conge($this->db, []);

        $this->array($cogne->getListe($date, $date, [], false))->isEmpty();
    }

    public function testGetListeFilled()
    {
        $statut = \App\Models\Conge::STATUT_VALIDATION_FINALE;
        $this->calling($this->result)->fetch_all = [[
            'u_prenom' => 'Perceval',
            'u_nom' => 'Karadoc',
            'p_demi_jour_deb' => 'pm',
            'p_demi_jour_fin' => 'am',
            'p_etat' => $statut,
            'p_date_deb' => '2017-02-12',
            'p_date_fin' => '2017-02-15'
        ],];

        $conge = new _Conge($this->db, []);

        $expected = [
            'P. Karadoc' => [
                [
                    'jour' => '2017-02-13',
                    'demiJournee' => '*',
                    'statut' => $statut,
                ], [
                    'jour' => '2017-02-14',
                    'demiJournee' => '*',
                    'statut' => $statut,
                ], [
                    'jour' => '2017-02-12',
                    'demiJournee' => 'pm',
                    'statut' => $statut,
                ], [
                    'jour' => '2017-02-15',
                    'demiJournee' => 'am',
                    'statut' => $statut,
                ],
            ],
        ];
        $date = new \DateTimeImmutable();

        $this->array($conge->getListe($date, $date, [], false))
            ->isIdenticalTo($expected);
    }
}
