<?php declare(strict_types = 1);
namespace LibertAPI\Planning;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use LibertAPI\Tools\Interfaces;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur de plannings
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 * @see \LibertAPI\Tests\Units\Planning\PlanningController
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le PlanningRepository
 */
final class PlanningController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable, Interfaces\IPostable, Interfaces\IPutable, Interfaces\IDeletable
{
    /**
     * {@inheritDoc}
     */
    protected function ensureAccessUser(string $order, \LibertAPI\Utilisateur\UtilisateurEntite $utilisateur)
    {
        $rights = [
            'getList' => $utilisateur->isResponsable() || $utilisateur->isHautResponsable() || $utilisateur->isAdmin(),
        ];

        if (isset($rights[$order]) && !$rights[$order]) {
            throw new \LibertAPI\Tools\Exceptions\MissingRightException('');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
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
            return $this->getResponseNotFound($response, 'Element « planning#' . $id . ' » is not a valid resource');
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
        $entites = array_map([$this, 'buildData'], $plannings);

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

    /**
     * {@inheritDoc}
     */
    public function post(IRequest $request, IResponse $response, array $routeArguments) : IResponse
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

    /**
     * {@inheritDoc}
     */
    public function put(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        $body = $request->getParsedBody();
        if (null === $body) {
            return $this->getResponseBadRequest($response, 'Body request is not a json content');
        }

        $id = (int) $arguments['planningId'];
        try {
            $planning = $this->repository->getOne($id);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « planning#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        try {
            $planning->populate($body);
            $this->repository->putOne($planning);
        } catch (MissingArgumentException $e) {
            return $this->getResponseMissingArgument($response);
        } catch (\DomainException $e) {
            return $this->getResponseBadDomainArgument($response, $e);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess($response, '', 204);
    }

    /**
     * {@inheritDoc}
     */
    public function delete(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        $id = (int) $arguments['planningId'];
        try {
            $planning = $this->repository->getOne($id);
            $this->repository->deleteOne($planning);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « planning#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess($response, '', 200);
    }
}
