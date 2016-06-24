<?php
namespace Tests\Units\utilisateur;

use \utilisateur\Fonctions as _Fonctions;
use \App\Models\Planning\Creneau;

/**
 * Classe de test des fonctions de l'utilisateur
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \utilisateur\Fonctions
 */
class Fonctions extends \Tests\Units\TestUnit
{

    /**
     * Test de la récupération d'un type de semaine pair
     */
    public function testGetRealWeekTypeEven()
    {
        $planningUser = [Creneau::TYPE_SEMAINE_PAIRE => []];
        $week         = date('W', strtotime('2016-01-15'));
        $this->getRealWeekType($planningUser, $week, Creneau::TYPE_SEMAINE_PAIRE);
    }

    /**
     * Test de la récupération d'un type de semaine impair
     */
    public function testGetRealWeekTypeOdd()
    {
        $planningUser = [Creneau::TYPE_SEMAINE_IMPAIRE => []];
        $week         = date('W', strtotime('2016-01-01'));
        $this->getRealWeekType($planningUser, $week, Creneau::TYPE_SEMAINE_IMPAIRE);
    }

    /**
     * Test de la récupération d'un type de semaine commun
     */
    public function testGetRealWeekTypeCommon()
    {
        $planningUser = [Creneau::TYPE_SEMAINE_COMMUNE => []];
        $week         = date('W', strtotime('2016-01-01'));
        $this->getRealWeekType($planningUser, $week, Creneau::TYPE_SEMAINE_COMMUNE);
    }

    /**
     * Asserteur sur les jours travaillés
     *
     * @param array $planningUser Tableau de planning
     * @param int   $week
     * @param bool  $expected     Résultat attendu de l'assertion
     */
    private function getRealWeekType(array $planningUser, $week, $expected)
    {
        $res = _Fonctions::getRealWeekType($planningUser, $week);

        $this->integer($res)->isIdenticalTo($expected);
    }

    /**
     * Test de l'absence de semaine de travail
     */
    public function testGetRealWeekTypeNone()
    {
        $planningUser = [54 => []];
        $res = _Fonctions::getRealWeekType($planningUser, 2);
        $this->integer($res)->isIdenticalTo(NIL_INT);
    }

    /**
     * Test de jour travaillé
     */
    public function testIsWorkginDayOk()
    {
        $planningWeek = [1 => []];
        $this->isWorkingDayAssert($planningWeek, 1, true);
    }

    /**
     * Test de jour non travaillé
     */
    public function testIsWorkingDayNOk()
    {
        $planningWeek = [2 => []];
        $this->isWorkingDayAssert($planningWeek, 1, false);
    }

    /**
     * Asserteur sur les jours travaillés
     *
     * @param array $planningWeek Tableau de la semaine de travail
     * @param int   $dayId        Id du jour (ISO 8601)
     * @param bool  $expected     Résultat attendu de l'assertion
     */
    private function isWorkingDayAssert(array $planningWeek, $dayId, $expected)
    {
        $res = _Fonctions::isWorkingDay($planningWeek, $dayId);

        $this->boolean($res)->isIdenticalTo($expected);
    }

    /**
     * Test de matin travaillé
     */
    public function testIsWorkingMorningOk()
    {
        $planningDay = [Creneau::TYPE_PERIODE_MATIN => []];
        $this->isWorkingMorning($planningDay, true);
    }

    /**
     * Test de matin non travaillé
     */
    public function testIsWorkingMorningNOk()
    {
        $planningDay = [Creneau::TYPE_PERIODE_APRES_MIDI => []];
        $this->isWorkingMorning($planningDay, false);
    }

    /**
     * Asserteur sur les matin travaillés
     *
     * @param array $planningDay Tableau de la journée de travail
     * @param bool  $expected    Résultat attendu de l'assertion
     */
    private function isWorkingMorning(array $planningDay, $expected)
    {
        $this->boolean(_Fonctions::isWorkingMorning($planningDay))->isIdenticalTo($expected);
    }

    /**
     * Test d'après midi travaillé
     */
    public function testIsWorkingAfternoonOk()
    {
        $planningDay = [Creneau::TYPE_PERIODE_APRES_MIDI => []];
        $this->isWorkingAfternoon($planningDay, true);
    }

    /**
     * Test d'après midi non travaillé
     */
    public function testIsWorkingAfternoonNOk()
    {
        $planningDay = [Creneau::TYPE_PERIODE_MATIN => []];
        $this->isWorkingAfternoon($planningDay, false);
    }

    /**
     * Asserteur sur les après midi travaillés
     *
     * @param array $planningDay Tableau de la journée de travail
     * @param bool  $expected    Résultat attendu de l'assertion
     */
    private function isWorkingAfternoon(array $planningDay, $expected)
    {
        $this->boolean(_Fonctions::isWorkingAfternoon($planningDay))->isIdenticalTo($expected);
    }
}
