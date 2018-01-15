<?php
namespace LibertAPI\Tools\Exceptions;

/**
 * Exception déclenchée en cas de valeur manquante
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté que par Planning\Repository
 * Ne devrait contacter personne
 */
class MissingArgumentException extends \RuntimeException
{
}
