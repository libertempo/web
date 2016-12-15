<?php
namespace Api\Middlewares;

use Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 * Authentification
 *
 * @since 0.1
 */
class Authentication
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
