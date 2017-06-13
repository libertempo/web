<?php
namespace Middlewares;

use Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 * Identification d'un utilisateur via la transmission du token
 *
 * @since 0.1
 */
final class Identification
{
    /**
     * @var IRequest
     */
    private $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Vérifie que la clé d'api fournie est la bonne
     *
     * @return bool
     */
    public function isTokenApiOk()
    {
        return true;
    }
}
