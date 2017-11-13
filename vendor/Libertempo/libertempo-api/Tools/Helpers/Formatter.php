<?php
namespace LibertAPI\Tools\Helpers;

/**
 * Formatage de petits utilitaires
 *
 * @since 0.1
 * @see \Tests\Units\Tools\Helpers\Formatter
 */
class Formatter
{
    /**
     * Change un texte du format « snake_case » au format « StudlyCaps »
     *
     * @param string $terme
     *
     * @return string
     */
    public static function getStudlyCapsFromSnake($terme)
    {
        $longueurTerme = strlen($terme);
        $studly = '';
        $i = 0;
        while ($i < $longueurTerme) {
            if ('_' === $terme[$i]) {
                if (($i + 1) < $longueurTerme) {
                    $studly .= ucfirst($terme[$i + 1]);
                }
                if (($i + 2) < $longueurTerme) {
                    $i = $i+2;
                } else {
                    ++$i;
                }
            } else {
                $studly .= $terme[$i];
                ++$i;
            }
        }

        return ucfirst($studly);
    }

    /**
     * Met au singulier un terme
     *
     * Comme il n'y a aucun moyen rapide de distinguer un « s / x » final légitime
     * d'un mot au pluriel, fait la brute épaisse
     *
     * @param string $terme
     *
     * @return string
     */
    public static function getSingularTerm($terme)
    {
        $len = strlen($terme);
        if (in_array($terme[$len - 1], ['s', 'x'], true)) {
            return substr($terme, 0, $len - 1);
        }

        return $terme;
    }

    /**
     * Fourni la datetime au format SQL d'un timestamp donné
     *
     * @param int $timestamp
     *
     * @return string
     * @since 0.3
     */
    public static function timeToSQLDatetime($timestamp)
    {
        return date('Y-m-d H:i', $timestamp);
    }
}
