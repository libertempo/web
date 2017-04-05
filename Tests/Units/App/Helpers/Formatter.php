<?php
namespace Tests\Units\App\Helpers;

use \App\Helpers\Formatter as _Formatter;

/**
 * Classe de test des formatages
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \App\Helpers\Formatter
 */
class Formatter extends \Tests\Units\TestUnit
{
    /**
     * Test de la transformation d'une date Fr -> ISO avec une date mal formée
     *
     * @return void
     * @since 1.9
     */
    public function testDateFr2IsoBadFormat()
    {
        $string = 'test';

        $this->exception(function () use ($string) {
            _Formatter::dateFr2Iso($string);
        })->isInstanceOf('\Exception');
    }

    /**
     * Test de la transformation d'une date Fr -> ISO avec une date bien formée
     *
     * @return void
     * @since 21.9
     */
    public function testDateFr2IsoWithoutError()
    {
        $date = '25/12/1969';

        $return = _Formatter::dateFr2Iso($date);

        $this->string($return)->isIdenticalTo('1969-12-25');
    }

    /**
     * Test de la transformation d'une date ISO -> Fr avec une date mal formée
     *
     * @return void
     * @since 1.9
     */
    public function testDateIso2FrBadFormat()
    {
        $string = 'test';

        $this->exception(function () use ($string) {
            _Formatter::dateIso2Fr($string);
        })->isInstanceOf('\Exception');
    }

    /**
     * Test de la transformation d'une date ISO -> Fr avec une date bien formée
     *
     * @access public
     * @return void
     * @since  1.9
     */
    public function testDateIso2FrWithoutError()
    {
        $date = '5555-33-22';

        $return = _Formatter::dateIso2Fr($date);

        $this->string($return)->isIdenticalTo('22/33/5555');
    }

    /**
     * Test de la transformation d'une heure en un timestamp avec du texte
     *
     * @return void
     * @since 1.9
     */
    public function testHour2TimeWithString()
    {
        $this->hour2TimeBadFormat('test');
    }

    /**
     * Test de la transformation d'une heure en un timestamp avec une heure aberrante
     *
     * @return void
     * @since 1.9
     */
    public function testHour2TimeOutOfBounds()
    {
        $this->hour2TimeBadFormat('88:88');
    }

    /**
     * Test de la transformation d'une heure en un timestamp avec une heure mal formée
     *
     * @param string $string
     *
     * @return void
     * @since 1.9
     */
    private function hour2TimeBadFormat($string)
    {
        $this->exception(function () use ($string) {
            _Formatter::hour2Time($string);
        })->isInstanceOf('\Exception');
    }

    /**
     * Test de la transformation d'une heure bien formée en un timestamp
     *
     * @return void
     * @since 1.9
     */
    public function testHour2TimeWithoutError()
    {
        $string = '12:59';

        $time = _Formatter::hour2Time($string);

        $this->string(date('d-m-Y H\:i', $time))->isIdenticalTo('01-01-1970 12:59');
    }

    /**
     * Test une heure mal formée
     *
     * @return void
     * @since 1.9
     */
    public function testisHourFormatBad()
    {
        $return = _Formatter::isHourFormat('12:');
        $this->integer($return)->isEqualTo(0);

        $return = _Formatter::isHourFormat('a12:25');
        $this->integer($return)->isEqualTo(0);

        $return = _Formatter::isHourFormat('12h00');
        $this->integer($return)->isEqualTo(0);
    }

    /**
     * Test des heures aberrantes
     *
     * @return void
     * @since 1.9
     */
    public function testisHourFormatOutOfBound()
    {
        $return = _Formatter::isHourFormat('20:70');
        $this->integer($return)->isEqualTo(0);

        $return = _Formatter::isHourFormat('25:00');
        $this->integer($return)->isEqualTo(0);
    }

    /**
     * Test une heure bien formée
     *
     * @return void
     * @since 1.9
     */
    public function testisHourFormatGood()
    {
        $string = '12:00';

        $return = _Formatter::isHourFormat($string);

        $this->integer($return)->isEqualTo(1);
    }

    /**
     * Test la transformation d'un nombre de secondes positif en hh:ii
     *
     * @since 1.9
     */
    public function testPositifTimestamp2Duree()
    {
        $ts = '3600';

        $time = _Formatter::timestamp2Duree($ts);

        $this->string($time)->isIdenticalTo('01:00');
    }

    /**
     * Test la transformation d'un timestamp équivalent à 0
     *
     * @since 1.9
     */
    public function testZeroTimestamp2Duree()
    {
        $ts = '0';

        $time = _Formatter::timestamp2Duree($ts);

        $this->string($time)->isIdenticalTo('00:00');
    }

    /**
    * Test la transformation d'un nombre de secondes négatif en hh:ii
     *
     * @since 1.9
     */
    public function testNegatifTimestamp2Duree()
    {
        $ts = - 3600;

        $time = _Formatter::timestamp2Duree($ts);

        $this->string($time)->isIdenticalTo('-01:00');
    }
}
