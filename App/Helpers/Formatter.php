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
            throw new \Exception(_('Date_mal_formee'));
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
            throw new \Exception(_('Date_mal_formee'));
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
            throw new \Exception(_('Heure_mal_formee'));
        }

        return ($matches[1]*3600+$matches[5]*60);
    }

    /**
     * Verifie le format d'une heure (hh:mm)
     *
     * @param string $heure
     *
     * @access public
     * @static
     * @return int
     * @since  1.9
     */
    public static function isHourFormat($heure)
    {
        $pattern = '/^(((0?|1)[0-9])|(2[0-3])):[0-5][0-9]$/';
        return preg_match($pattern, $heure);
    }

    /**
     * Convertit un timestamp en une durée
     *
     * @param string $secondes
     *
     * @access public
     * @static
     * @return string
     * @since  1.9
     */
    public static function timestamp2Duree($timestamp)
    {
        $timestamp = (int) $timestamp;
        $secondes = abs($timestamp/60%60);
        $heures = abs($timestamp/3600);
        $duree = sprintf('%02d:%02d', $heures, $secondes);

        return (0 <= $timestamp)
            ? $duree
            : '-' . $duree
        ;
    }

    public static function roundToHalf($num)
    {
        if (!is_numeric($num)) {
            return $num;
        }

        $ceil = ceil($num);
        $half = $ceil- 0.5;
        if ($num >= $half + 0.25) {
            return $ceil;
        } elseif ($num < $half - 0.25) {
            return floor($num);
        }
        
        return $half;
    }
}
