<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Exceptions;

/**
 * Exception déclenchée en cas de ressource inconnue manquante
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.1
 *
 * Ne devrait être contacté que par *Repository
 * Ne devrait contacter personne
 */
class UnknownResourceException extends \Exception
{
}
