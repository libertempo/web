<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Exceptions;

/**
 * Exception déclenchée en cas de requête HTTP incorrecte
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.1
 *
 * Ne devrait être contacté que par Tools\Services\*AuthentifierService
 * Ne devrait contacter personne
 */
class BadRequestException extends \RuntimeException
{
}
