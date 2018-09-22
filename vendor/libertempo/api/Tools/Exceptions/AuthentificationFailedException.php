<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Exceptions;

/**
 * Exception déclenchée en cas d'authentification échouée
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since  1.1
 * Ne devrait être contacté que par AuthentificationController
 * Ne devrait contacter personne
 */
class AuthentificationFailedException extends \UnexpectedValueException
{
}
