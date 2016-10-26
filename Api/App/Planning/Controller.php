<?php
namespace Api\App\Planning;


/**
 * Contrôleur de plannings
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 */
final class Controller extends \Api\App\Libraries\Controller
{
    /**
     *
     */
    public function getAvailablesMethods()
    {
        // peut être pas utile si les seules méthodes publiques sont les méthodes de request, faut voir
        return ['get'];
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Execute l'ordbre HTTP GET
     *
     * @return string JSON selon le format ['code', 'status', 'message', 'data']
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
     * @return string JSON bien formé
     */
    private function getOne($id)
    {
        $id = (int) $id;
        $data = [
            'code' => 404,
            'status' => 'error',
            'message' => 'Not Found',
            'data' => 'Element « ' . $id . ' » of « planning » is not a valid resource',
        ];
        return $this->response->withJson($data, 404);

        $data = [
            'code' => 200,
            'status' => 'success',
            'message' => ':-)',
            'data' => 'banana unique',
        ];

        return $this->response->withJson($data, 200);
    }

    /**
     * Retourne une collection de plannings
     *
     * @return string JSON bien formé
     */
    private function getList()
    {
        /**
         * querystring que pour GET et pour la recherche d'éléments !!
         */
        //$allGetVars = $this->request->getQueryParams();
        //var_dump($allGetVars);

        $data = [
            'code' => 200,
            'status' => 'success',
            'message' => ':-)',
            'data' => 'banana list',
        ];

        return $this->response->withJson($data, 200);
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
