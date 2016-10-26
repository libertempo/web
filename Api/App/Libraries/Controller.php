<?php
namespace Api\App\Libraries;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Contrôleur principal
 */
class Controller
{
    /**
     * @var ServerRequestInterface Requête HTTP
     */
    protected $request;

    /**
     * @var ResponseInterface Réponse HTTP
     */
    protected $response;

    public function __construct(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;

        if (!$this->isApiKeyOk()) {
            return $this->getResponseErrorAuthentication();
        }
        //$this->checkApiKey();
        //$this->getUser();

        // check api keys, else 401
        // get user by Id given
        // check if user authorized to access to the resource, else 403
    }

    /**
     *
     */
    private function isApiKeyOk()
    {

    }

    /**
     *
     */
    private function getResponseErrorAuthentication()
    {

    }

    /**
     * 
     */
    private function isResourceXXXForUser()
    {

    }

    /**
     *
     */
    private function getResponseErrorAccess()
    {

    }
}
