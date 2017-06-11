<?php
namespace Tests\Units\App\Libraries\Calendrier\Evenements;

/**
 * Classe de test des collections d'heures
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
abstract class AHeure extends \Tests\Units\TestUnit
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

        $testedClass = $this->getTestedClass();
        $heures = new $testedClass($this->db, []);

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

        $testedClass = $this->getTestedClass();
        $heures = new $testedClass($this->db, []);

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

    /**
     * Retourne le namespace de la classe à tester
     *
     * @return string
     */
    abstract protected function getTestedClass();
}
