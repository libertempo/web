<?php
namespace Tests\Units\App\Helpers;

use \App\Helpers\Formatter as _Formatter;
use \atoum;

/**
 * Classe de test des formatages
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \App\Helpers\Formatter
 */
class Formatter extends atoum
{
    /**
     * Test de la transformation d'une date Fr -> ISO avec une date mal formée
     *
     * @return void
     * @access public
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
     * @access public
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
     * @access public
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
}
