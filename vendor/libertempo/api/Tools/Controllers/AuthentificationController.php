<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Controllers;

use LibertAPI\Utilisateur\UtilisateurRepository;
use Slim\Interfaces\RouterInterface as IRouter;
use LibertAPI\Tools\Interfaces;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur de l'authentification
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class AuthentificationController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable
{
    public function __construct(\LibertAPI\Utilisateur\UtilisateurRepository $repository, IRouter $router)
    {
        parent::__construct($repository, $router);
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        $authentificationType = 'Basic';
        $authentification = $request->getHeaderLine('Authorization');
        if (0 !== stripos($authentification, $authentificationType)) {
            return $this->getResponseBadRequest($response, 'Authorization mechanism is not set to « ' . $authentificationType . ' »');
        }

        $authentification = substr($authentification, strlen($authentificationType) + 1);
        list($login, $password) = explode(':', base64_decode($authentification));

        try {
            $utilisateur = $this->repository->find([
                'login' => $login,
                'isActif' => true,
            ]);
            if (!$utilisateur->isPasswordMatching($password)) {
                throw new \UnexpectedValueException('Wrong password');
            }
            $utilisateurUpdated = $this->repository->regenerateToken($utilisateur);
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNotFound($response, 'No user matches these criteria');
        }

        return $this->getResponseSuccess(
            $response,
            $utilisateurUpdated->getToken(),
            200
        );
    }
}
