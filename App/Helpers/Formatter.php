<?php
namespace App\Helpers;

/**
 * Classe de formatage de petits utilitaires de l'application
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @see    \Tests\Units\App\Helpers\Formatter
 */
class Formatter
{
    /**
     * Transforme une date du format français (dd/mm/YYY) au format ISO (YYYY-mm-dd)
     *
     * @param string $date
     *
     * @access public
     * @static
     * @return string
     * @throws \Exception Si le paramètre d'entrée n'est pas bien formé
     * @TODO   \Exception out of bounds ?
     * @since  1.9
     */
    public static function dateFr2Iso($date)
    {
        if (!preg_match('#^(\d{2})/(\d{2})/(\d{4})$#', $date, $matches)) {
            throw new \Exception('Date mal formée');
        }

        return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
    }

    /**
     * Transforme une date du format ISO (YYYY-mm-dd) au format français (dd/mm/YYYY)
     *
     * @param string $date
     *
     * @access public
     * @static
     * @return string
     * @throws \Exception Si le paramètre d'entrée n'est pas bien formé
     * @TODO   \Exception out of bounds ?
     * @since  1.9
     */
    public static function dateIso2Fr($date)
    {
        if (!preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $date, $matches)) {
            throw new \Exception('Date mal formée');
        }

        return $matches[3] . '/' . $matches[2] . '/' . $matches[1];
    }

    /**
     * Transforme une heure en un timestamp positionné sur le début du temps unix
     *
     * @param string $hour
     *
     * @return int
     * @throws \Exception si le paramètre d'entrée n'est pas bien formé
     * @since  1.9
     */
    public static function hour2Time($hour)
    {
        $pattern = '/^(((0?|1)[0-9])|(2[0-3])):([0-5][0-9])$/';
        if (!preg_match($pattern, $hour, $matches)) {
            throw new \Exception('Heure mal formée');
        }

        return mktime($matches[1], $matches[5], 0, 1, 1, 70);
    }
}
