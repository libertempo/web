<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Middlewares;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use LibertAPI\Tools\Libraries\AControllerFactory;

/**
 * Construction du contrôleur pour le DIC.
 * N'est PAS un builder au sens d'un Design Pattern.
 *
 * @since 1.0
 */
final class ControllerBuilder extends \LibertAPI\Tools\AMiddleware
{
    public function __invoke(IRequest $request, IResponse $response, callable $next) : IResponse
    {
        $container = $this->getContainer();
        $reserved = ['HelloWorld'];
        $storage = $container->get('storageConnector');
        $router = $container->get('router');
        $ressourcePath = str_replace('|', '\\', $request->getAttribute('nomRessources'));
        if (in_array($ressourcePath, $reserved, true)) {
            return $next($request, $response);
        }

        try {
            if ('Authentification' === $ressourcePath) {
                $controller = AControllerFactory::createControllerAuthentification(
                    $ressourcePath,
                    $storage,
                    $router
                );
            } else {
                $controller = AControllerFactory::createControllerWithUser(
                    $ressourcePath,
                    $storage,
                    $router,
                    $container->get('currentUser')
                );
            }
            $container->set('controller', $controller);
        } catch (\DomainException $e) {
            return call_user_func(
                $container->get('notFoundHandler'),
                $request,
                $response
            );
        }

        return $next($request, $response);
    }
}
