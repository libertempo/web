<?php
namespace Api\App\Planning;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Contrôleur de plannings
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le Planning\Repository
 */
final class Controller extends \Api\App\Libraries\Controller
{
    /**
     * {@inheritDoc}
     */
    public function getAvailablesMethods()
    {
        // peut être pas utile si les seules méthodes publiques sont les méthodes de request, faut voir
        return ['get'];
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceName()
    {
        return 'plannings';
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Execute l'ordre HTTP GET
     *
     * @return ResponseInterface
     */
    public function get($id = -1)
    {
        if (-1 === $id) {
            return $this->getList();
        }

        return $this->getOne($id);
    }

    /**
     * Retourne un élément unique
     *
     * @param int $id ID de l'élément
     *
     * @return ResponseInterface
     */
    private function getOne($id)
    {
        $id = (int) $id;
        $planning = $this->repository->getOne($id);
        $code = -1;
        $data = [];

        if (empty($planning)) {
            $code = 404;
            $data = [
                'code' => $code,
                'status' => 'error',
                'message' => 'Not Found',
                'data' => 'Element « ' . $id . ' » of « ' . $this->getResourceName() . ' » is not a valid resource',
            ];
        } else {
            $code = 200;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => [],
            ];
        }

        return $this->response->withJson($data, $code);
    }

    /**
     * Retourne une collection de plannings
     *
     * @return ResponseInterface
     */
    private function getList()
    {
        $plannings = $this->repository->getList(
            $this->request->getQueryParams()
        );
        $code = -1;
        $data = [];

        if (empty($plannings)) {
            $code = 404;
            $data = [
                'code' => $code,
                'status' => 'error',
                'message' => 'Not Found',
                'data' => ' « ' . $this->getResourceName() . ' » is not a valid resource',
            ];
        } else {
            $code = 200;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => [],
            ];
        }

        return $this->response->withJson($data, $code);
    }

    /*************************************************
     * OPTIONS
     *************************************************/

/*
    public function options()
    {
        $data = [
            'code' => 200,
            'status' => 'success',
            'message' => ':-)',
            'data' => $this->getAvailablesMethods(),
        ];

        return $this->response->withJson($data, 200);
    }
    */
}
