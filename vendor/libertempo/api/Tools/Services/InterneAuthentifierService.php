<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Services;

use LibertAPI\Tools\Libraries\ARepository;
use Psr\Http\Message\ServerRequestInterface as IRequest;

/**
 * Service d'authentification interne (dbconges)
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.1
 */
class InterneAuthentifierService extends AAuthentifierFactoryService
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
        $utilisateur = $this->getRepository()->find([
            'login' => $this->getLogin(),
            'isActif' => true,
        ]);

        return $utilisateur->isPasswordMatching($this->getPassword());
    }
}
