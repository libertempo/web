<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Middlewares;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use \LibertAPI\Tools\Libraries\AEntite;
use \LibertAPI\Tools\Libraries\ARepository;
use \LibertAPI\Tools\Helpers\Formatter;
use \LibertAPI\Utilisateur;

/**
 * Réalise l'identification. En profite pour pinger.
 *
 * @since 1.0
 */
final class Identificator extends \LibertAPI\Tools\AMiddleware
{
    public function __invoke(IRequest $request, IResponse $response, callable $next) : IResponse
    {
        $container = $this->getContainer();
        $repoUtilisateur = $container->get(Utilisateur\UtilisateurRepository::class);
        $openedRoutes = ['Authentification', 'HelloWorld'];
        $ressourcePath = $request->getAttribute('nomRessources');
        if (in_array($ressourcePath, $openedRoutes, true)) {
            return $next($request, $response);
        } elseif ($this->isIdentificationOK($request, $repoUtilisateur)) {
             // Ping de last_access
            $utilisateur = $repoUtilisateur->updateDateLastAccess($this->utilisateur);

            $container->set('currentUser', $utilisateur);
            return $next($request, $response);
        }

        return call_user_func(
            $container->get('unauthorizedHandler'),
            $request,
            $response
        );
    }

    private function isIdentificationOK(IRequest $request, ARepository $repository) : bool
    {
        $token = $request->getHeaderLine('Token');
        if (empty($token)) {
            return false;
        }
        try {
            $this->utilisateur = $repository->find([
                'token' => $token,
                'gt_date_last_access' => $this->getDateLastAccessAuthorized(),
                'isActif' => true,
            ]);
            return $this->utilisateur instanceof AEntite;
        } catch (\UnexpectedValueException $e) {
            return false;
        }
    }

    /**
     * Retourne la date limite de dernier accès pour être considéré en ligne
     *
     * @return string
     */
    private function getDateLastAccessAuthorized() : string
    {
        return Formatter::timeToSQLDatetime(time() - static::DUREE_SESSION);
    }

    /**
     * @var AEntite | null
     */
    private $utilisateur;

    /**
     * @var int Durée de validité du token fourni, en secondes
     */
    const DUREE_SESSION = 30*60;
}
