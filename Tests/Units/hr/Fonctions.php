<?php
namespace Tests\Units\hr;

/**
 * Classe de test des fonctions du HR
 *
 * @since 1.11
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Fonctions extends \Tests\Units\TestUnit
{
    /**
     * Test le bon calcul auto des jours féries
     *
     * @dataProvider providerJourFeries
     */
    public function testFcListJourFeries($annee, $joursFeries)
    {
        $class = $this->testedClass->getClass();
        $this->array(array_values($class::fcListJourFeries($annee)))
            ->isIdenticalTo($joursFeries);
    }

    protected function providerJourFeries()
    {
        return [
            [
                '2018', [
                    '2018-01-01',
                    '2018-04-01',
                    '2018-04-02',
                    '2018-05-01',
                    '2018-05-08',
                    '2018-05-10',
                    '2018-07-14',
                    '2018-08-15',
                    '2018-11-01',
                    '2018-11-11',
                    '2018-12-25',
                ],
            ],
            [
                '2019', [
                    '2019-01-01',
                    '2019-04-21',
                    '2019-04-22',
                    '2019-05-01',
                    '2019-05-08',
                    '2019-05-30',
                    '2019-07-14',
                    '2019-08-15',
                    '2019-11-01',
                    '2019-11-11',
                    '2019-12-25',
                ],
            ]
        ];
    }
}
