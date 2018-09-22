<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Services;

use Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 *
 */
class LdapAuthentifierService extends AAuthentifierFactoryService
{
    public function isAuthentificationSucceed(IRequest $request) : bool
    {
        return false;
    }

    public function getLogin() : string
    {
        return '';
    }
}
