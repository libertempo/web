<?php
namespace App\Components\Planning;

use App\Exceptions\MissingArgumentException;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur de plannings
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Tests\Units\App\Components\Planning\Controller
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le Planning\Repository
 */
final class Controller extends \App\Libraries\AController
{
    /*************************************************
     * GET
     *************************************************/

    /**
     * Execute l'ordre HTTP GET
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     * @param array $arguments Arguments de route
     *
     * @return IResponse
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    public function get(IRequest $request, IResponse $response, array $arguments)
    {
        if (!isset($arguments['planningId'])) {
            return $this->getList($request, $response);
        }

        return $this->getOne($response, (int) $arguments['planningId']);
    }

    /**
     * Retourne un élément unique
     *
     * @param IResponse $response Réponse Http
     * @param int $id ID de l'élément
     *
     * @return IResponse, 404 si l'élément n'est pas trouvé, 200 sinon
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    private function getOne(IResponse $response, $id)
    {
        try {
            $planning = $this->repository->getOne($id);
            $code = 200;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => $this->buildData($planning),
            ];

            return $response->withJson($data, $code);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « plannings#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Retourne un tableau de plannings
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     *
     * @return IResponse
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    private function getList(IRequest $request, IResponse $response)
    {
        try {
            $plannings = $this->repository->getList(
                $request->getQueryParams()
            );
            $models = [];
            foreach ($plannings as $planning) {
                $models[] = $this->buildData($planning);
            }
            $code = 200;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => $models,
            ];

            return $response->withJson($data, $code);
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNotFound($response, 'No result');
        } catch (\Exception $e) {
            throw $e;
        }
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
        return [
            'id' => $model->getId(),
            'name' => $model->getName(),
            'status' => $model->getStatus(),
        ];
    }

    /*************************************************
     * POST
     *************************************************/

     /**
      * Execute l'ordre HTTP POST
      *
      * @param IRequest $request Requête Http
      * @param IResponse $response Réponse Http
      *
      * @return IResponse
      * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
      */
    public function post(IRequest $request, IResponse $response)
    {
        $body = $request->getParsedBody();
        if (null === $body) {
            return $this->getResponseBadRequest($response, 'Body request is not a json content');
        }

        try {
            $planningId = $this->repository->postOne($body, new Model([]));
            $code = 201;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => $this->router->pathFor('getPlanningDetail', [
                    'planningId' => $planningId
                ]),
            ];

            return $response->withJson($data, $code);
        } catch (MissingArgumentException $e) {
            return $this->getResponseMissingArgument($response);
        } catch (\DomainException $e) {
            return $this->getResponseBadDomainArgument($response, $e);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /*************************************************
     * PUT
     *************************************************/

    /**
     * Execute l'ordre HTTP PUT
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     * @param array $arguments Arguments de route
     *
     * @return IResponse
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    public function put(IRequest $request, IResponse $response, array $arguments)
    {
        $body = $request->getParsedBody();
        if (null === $body) {
            return $this->getResponseBadRequest($response, 'Body request is not a json content');
        }

        $id = (int) $arguments['planningId'];
        try {
            $planning = $this->repository->getOne($id);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « plannings#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $this->repository->putOne($body, $planning);
            $code = 204;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => '',
            ];

            return $response->withJson($data, $code);
        } catch (MissingArgumentException $e) {
            return $this->getResponseMissingArgument($response);
        } catch (\DomainException $e) {
            return $this->getResponseBadDomainArgument($response, $e);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /*************************************************
     * DELETE
     *************************************************/

     /**
      * Execute l'ordre HTTP DELETE
      *
      * @param IRequest $request Requête Http
      * @param IResponse $response Réponse Http
      * @param array $arguments Arguments de route
      *
      * @return IResponse
      * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
      */
    public function delete(IRequest $request, IResponse $response, array $arguments)
    {
        $id = (int) $arguments['planningId'];
        try {
            $planning = $this->repository->getOne($id);
            $this->repository->deleteOne($planning);
            $code = 200;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => '',
            ];

            return $response->withJson($data, $code);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « plannings#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
