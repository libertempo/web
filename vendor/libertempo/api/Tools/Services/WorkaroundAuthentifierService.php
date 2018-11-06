<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Services;

use LibertAPI\Tools\Libraries\ARepository;
use Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 * Service d'authentification de contournement temporaire de CAS et SSO
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.3
 */
class WorkaroundAuthentifierService extends AAuthentifierFactoryService
{
    public function __construct(ARepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthentificationSucceed(IRequest $request) : bool
    {
        $this->storeBasicIdentificants($request);
        $this->setPassword('none');
        $utilisateur = $this->getRepository()->find([
            'login' => $this->getLogin(),
            'isActif' => true,
        ]);

        return $utilisateur->isPasswordMatching($this->getPassword());
    }
}
