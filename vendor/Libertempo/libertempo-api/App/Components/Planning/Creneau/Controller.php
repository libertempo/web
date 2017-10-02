<?php
namespace App\Components\Planning\Creneau;

use App\Exceptions\MissingArgumentException;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur des creneaux de plannings
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
     *
     * @return IResponse
     */
    public function get(IRequest $request, IResponse $response, array $arguments)
    {
        if (!isset($arguments['creneauId'])) {
            return $this->getList($request, $response, (int) $arguments['planningId']);
        }

        return $this->getOne($response, (int) $arguments['creneauId'], (int) $arguments['planningId']);
    }

    /**
     * Retourne un élément unique
     *
     * @param IResponse $response Réponse Http
     * @param int $id ID de l'élément
     * @param int $planningId Contrainte de recherche sur le planning
     *
     * @return IResponse, 404 si l'élément n'est pas trouvé, 200 sinon
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    private function getOne(IResponse $response, $id, $planningId)
    {
        $code = -1;
        $data = [];
        try {
            $creneau = $this->repository->getOne($id, $planningId);
            $code = 200;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => $this->buildData($creneau),
            ];

            return $response->withJson($data, $code);
        } catch (\DomainException $e) {
            $code = 404;
            $data = [
                'code' => $code,
                'status' => 'error',
                'message' => 'Not Found',
                'data' => 'Element « creneaux#' . $id . ' » is not a valid resource',
            ];

            return $response->withJson($data, $code);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Retourne un tableau de plannings
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     * @param int $planningId Contrainte de recherche sur le planning
     *
     * @return IResponse
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    private function getList(IRequest $request, IResponse $response, $planningId)
    {
        $code = -1;
        $data = [];
        try {
            $creneaux = $this->repository->getList(['planningId' => $planningId]);
            $models = [];
            foreach ($creneaux as $creneau) {
                $models[] = $this->buildData($creneau);
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
            $code = 404;
            $data = [
                'code' => $code,
                'status' => 'error',
                'message' => 'Not Found',
                'data' => 'No result',
            ];

            return $response->withJson($data, $code);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Construit le « data » du json
     *
     * @param Model $model Créneau de planning
     *
     * @return array
     */
    private function buildData(Model $model)
    {
        return [
            'id' => $model->getId(),
            'planningId' => $model->getPlanningId(),
            'jourId' => $model->getJourId(),
            'typeSemaine' => $model->getTypeSemaine(),
            'typePeriode' => $model->getTypePeriode(),
            'debut' => $model->getDebut(),
            'fin' => $model->getFin(),
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
            $creneauxIds = $this->repository->postList($body, new Model([]));
            $dataMessage = [];
            foreach ($creneauxIds as $id) {
                $dataMessage[] = $this->router->pathFor('getPlanningCreneauDetail', [
                    'creneauId' => $id,
                ]);
            }
            $code = 201;
            $data = [
                'code' => $code,
                'status' => 'success',
                'message' => '',
                'data' => $dataMessage,
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

        $id = (int) $arguments['creneauId'];
        try {
            $creneau = $this->repository->getOne($id);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « creneaux#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $this->repository->putOne($body, $creneau);
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
}
