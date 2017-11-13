<?php
namespace LibertAPI\Planning;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur de plannings
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \Tests\Units\Planning\Controller
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le Planning\Repository
 */
final class PlanningController extends \LibertAPI\Tools\Libraries\AController
{
    /**
     * {@inheritDoc}
     */
    protected function ensureAccessUser($order, \LibertAPI\Utilisateur\UtilisateurEntite $utilisateur)
    {
        $rights = [
            'getList' => $utilisateur->isResponsable(),
        ];

        if (isset($rights[$order]) && !$rights[$order]) {
            throw new \LibertAPI\Tools\Exceptions\MissingRightException('');
        }
    }

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
     * @return IResponse
     */
    private function getOne(IResponse $response, $id)
    {
        try {
            $planning = $this->repository->getOne($id);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « plannings#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess(
            $response,
            $this->buildData($planning),
            200
        );
    }

    /**
     * Retourne un tableau de plannings
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     *
     * @return IResponse
     */
    private function getList(IRequest $request, IResponse $response)
    {
        try {
            $this->ensureAccessUser(__FUNCTION__, $this->currentUser);
            $plannings = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\LibertAPI\Tools\Exceptions\MissingRightException $e) {
            return $this->getResponseForbidden($response, $request);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = [];
        foreach ($plannings as $planning) {
            $entites[] = $this->buildData($planning);
        }

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param PlanningEntite $entite Planning
     *
     * @return array
     */
    private function buildData(PlanningEntite $entite)
    {
        return [
            'id' => $entite->getId(),
            'name' => $entite->getName(),
            'status' => $entite->getStatus(),
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
      */
    public function post(IRequest $request, IResponse $response)
    {
        $body = $request->getParsedBody();
        if (null === $body) {
            return $this->getResponseBadRequest($response, 'Body request is not a json content');
        }

        try {
            $planningId = $this->repository->postOne($body, new PlanningEntite([]));
        } catch (MissingArgumentException $e) {
            return $this->getResponseMissingArgument($response);
        } catch (\DomainException $e) {
            return $this->getResponseBadDomainArgument($response, $e);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess(
            $response,
            $this->router->pathFor('getPlanningDetail', [
                'planningId' => $planningId
            ]),
            201
        );
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
            return $this->getResponseError($response, $e);
        }

        try {
            $this->repository->putOne($body, $planning);
        } catch (MissingArgumentException $e) {
            return $this->getResponseMissingArgument($response);
        } catch (\DomainException $e) {
            return $this->getResponseBadDomainArgument($response, $e);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess($response, '', 204);
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
      */
    public function delete(IRequest $request, IResponse $response, array $arguments)
    {
        $id = (int) $arguments['planningId'];
        try {
            $planning = $this->repository->getOne($id);
            $this->repository->deleteOne($planning);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « plannings#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess($response, '', 200);
    }
}
