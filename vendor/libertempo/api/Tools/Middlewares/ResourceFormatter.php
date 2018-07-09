<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Middlewares;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use \LibertAPI\Tools\Helpers\Formatter;

/**
 * Découvre et met en forme les noms des ressources
 *
 * @since 1.0
 */
final class ResourceFormatter extends \LibertAPI\Tools\AMiddleware
{
    public function __invoke(IRequest $request, IResponse $response, callable $next) : IResponse
    {
        $path = trim(trim($request->getUri()->getPath()), '/');
        $api = 'api/';
        $position = mb_stripos($path, $api);
        if (false !== $position) {
            $uriUpdated = $request->getUri()->withPath('/' . substr($path, $position + strlen($api)));
            $request = $request->withUri($uriUpdated);
            $path = trim(trim($request->getUri()->getPath()), '/');
        }
        $paths = explode('/', $path);
        $ressources = [];
        foreach ($paths as $value) {
            if (!is_numeric($value)) {
                $ressources[] = Formatter::getStudlyCapsFromSnake($value);
            }
        }
        $request = $request->withAttribute('nomRessources', implode('|', $ressources));

        return $next($request, $response);
    }
}
