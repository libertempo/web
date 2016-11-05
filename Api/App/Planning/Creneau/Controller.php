<?php
namespace Api\App\Planning\Creneau;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur des creneaux de plannings
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Api\Tests\Units\App\Planning\Controller
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le Planning\Repository
 */
final class Controller extends \Api\App\Libraries\Controller
{
    /*************************************************
     * GET
     *************************************************/

    public function post(IRequest $request, IResponse $response, array $arguments)
    {
    }

    /**
     * Execute l'ordre HTTP GET
     *
     * @return IResponse
     */
    public function get(IRequest $request, IResponse $response, array $arguments)
    {
    }

    /**
     * Retourne un élément unique
     *
     * @param int $id ID de l'élément
     *
     * @return IResponse, 404 si l'élément n'est pas trouvé, 200 sinon
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    private function getOne($id)
    {
    }

    /**
     * Retourne un tableau de plannings
     *
     * @return IResponse
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    private function getList()
    {
    }

    /**
     * Construit le « data » du json
     *
     * @param Model $model Planning
     *
     * @return array
     */
    private function buildData(Model $model)
    {
    }
}
