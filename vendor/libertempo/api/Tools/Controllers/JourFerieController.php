<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Controllers;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use LibertAPI\Tools\Interfaces;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use \Slim\Interfaces\RouterInterface as IRouter;
use LibertAPI\JourFerie;

/**
 * Contrôleur de jour férié
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le JourFerieRepository
 */
final class JourFerieController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable
{
    public function __construct(JourFerie\JourFerieRepository $repository, IRouter $router)
    {
        parent::__construct($repository, $router);
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        return $this->getList($request, $response);
    }

    /**
     * Retourne un tableau de jours fériés
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     */
    private function getList(IRequest $request, IResponse $response) : IResponse
    {
        try {
            $jours = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = array_map([$this, 'buildData'], $jours);

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     */
    private function buildData(JourFerie\JourFerieEntite $entite) : array
    {
        return [
            'date' => $entite->getDate(),
        ];
    }
}
