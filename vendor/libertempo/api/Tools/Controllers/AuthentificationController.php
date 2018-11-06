<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Controllers;

use LibertAPI\Utilisateur\UtilisateurRepository;
use LibertAPI\Tools\Exceptions\BadRequestException;
use LibertAPI\Tools\Exceptions\AuthentificationFailedException;
use LibertAPI\Tools\Libraries\StorageConfiguration;
use LibertAPI\Tools\Services\AAuthentifierFactoryService;
use Slim\Interfaces\RouterInterface as IRouter;
use LibertAPI\Tools\Interfaces;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur de l'authentification
 *
 * Depuis la 1.1, n'est plus testable unitairement (les multiples authentifiers et la fabrique l'empêchent). À tester par des tests de services
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 */
final class AuthentificationController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable
{
    public function __construct(UtilisateurRepository $repository, IRouter $router, StorageConfiguration $configuration)
    {
        parent::__construct($repository, $router);
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        try {
            $authentifier = AAuthentifierFactoryService::getAuthentifier($this->configuration, $this->repository);
            // Ajout de la configuration dans ce contexte, pour les authentifiers
            $request = $request->withAttribute('configurationFileData', $arguments['configurationFileData']);
            if (!$authentifier->isAuthentificationSucceed($request)) {
                throw new AuthentificationFailedException();
            }
            $utilisateur = $this->repository->find([
                'login' => $authentifier->getLogin(),
                'isActif' => true,
            ]);
        } catch (BadRequestException $e) {
            return $this->getResponseBadRequest($response, 'Authorization header doesn\'t comply to authentication method');
        } catch (AuthentificationFailedException $e) {
            return $this->getResponseNotFound($response, 'No user matches these criteria');
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNotFound($response, 'No user matches these criteria');
        }

        $utilisateurUpdated = $this->repository->regenerateToken($utilisateur);

        return $this->getResponseSuccess(
            $response,
            $utilisateurUpdated->getToken(),
            200
        );
    }

    /**
     * @var StorageConfiguration
     */
    private $configuration;
}
