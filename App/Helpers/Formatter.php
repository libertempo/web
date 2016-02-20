<?php
namespace App\Libraries\Static;

/**
 * Classe de formatage de petits éléments de l'application
 *
 * @since 1.9
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
     */
    public static function dateFr2Iso()
    {

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
     */
    public static function dateIso2Fr($date)
    {
        if (!preg_match('#^\(d+)/$#', $date, $matches)) {
            throw new \Exception('Date mal formée');
        }
    }
}
