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
        $studlyCapsed = '';
        $termeSepare = explode('_', $terme);
        foreach ($termeSepare as $mot) {
            $studlyCapsed .= ucfirst($mot);
        }

        return $studlyCapsed;
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
        $format = 'Y-m-d H:i';
        $date = date($format, $timestamp);
        return (new \DateTimeImmutable($date))->format($format);
    }
}
