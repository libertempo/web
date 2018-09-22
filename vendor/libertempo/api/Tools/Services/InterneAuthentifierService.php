<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Services;

use LibertAPI\Tools\Libraries\ARepository;
use LibertAPI\Tools\Exceptions\BadRequestException;
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
        parent::__construct($repository);
    }

    /**
     * {@inheritDoc}
     */
    public function isAuthentificationSucceed(IRequest $request) : bool
    {
        $authentificationType = 'Basic';
        $authentification = $request->getHeaderLine('Authorization');
        if (0 !== stripos($authentification, $authentificationType)) {
            throw new BadRequestException();
        }

        $authentification = substr($authentification, strlen($authentificationType) + 1);
        list($this->login, $password) = explode(':', base64_decode($authentification));

        $utilisateur = $this->getRepository()->find([
            'login' => $this->login,
            'isActif' => true,
        ]);

        return $utilisateur->isPasswordMatching($password);
    }

    /**
     * {@inheritDoc}
     */
    public function getLogin() : string
    {
        return $this->login;
    }

    /**
     * @var string Login de l'utilisateur en cours de connexion
     */
    private $login = '';
}
