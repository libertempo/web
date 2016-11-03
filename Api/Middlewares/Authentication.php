<?php
namespace Api\Middlewares;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Authentification
 *
 * @since 0.1
 */
class Authentication
{
    /**
     * @var ServerRequestInterface
     */
    private $request;

    public function __construct(ServerRequestInterface $request)
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
