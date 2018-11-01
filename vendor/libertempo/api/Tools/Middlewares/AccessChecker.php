<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Middlewares;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use \LibertAPI\Tools\Helpers\Formatter;

/**
 * Vérifie les autorisations d'accès pour la route et l'utilisateur donnés
 *
 * @since 1.1
 */
final class AccessChecker extends \LibertAPI\Tools\AMiddleware
{
    public function __invoke(IRequest $request, IResponse $response, callable $next) : IResponse
    {
        $ressourcePath = $request->getAttribute('nomRessources');
        $container = $this->getContainer();

        switch ($ressourcePath) {
            case 'Absence|Type':
            case 'Utilisateur':
            case 'JourFerie':
            case 'Journal':
            case 'Authentification':
            case 'HelloWorld':
            case 'Planning|Creneau':
                return $next($request, $response);
            case 'Groupe':
            case 'Groupe|GrandResponsable':
            case 'Groupe|Responsable':
            case 'Groupe|Employe':
                $user = $container->get('currentUser');
                if (!$user->isAdmin()) {
                    return call_user_func(
                        $container->get('forbiddenHandler'),
                        $request,
                        $response
                    );
                }

                return $next($request, $response);
            case 'JourFerie':
                $user = $container->get('currentUser');
                if (!$user->isHautResponsable()) {
                    return call_user_func(
                        $container->get('forbiddenHandler'),
                        $request,
                        $response
                    );
                }

                return $next($request, $response);
            case 'Planning':
                $user = $container->get('currentUser');
                if (!$user->isResponsable() && !$user->isHautResponsable() && !$user->isAdmin()) {
                    return call_user_func(
                        $container->get('forbiddenHandler'),
                        $request,
                        $response
                   );
                }

                return $next($request, $response);
            default:
                throw new \RuntimeException('Rights were not configured for this route');
        }
    }
}
