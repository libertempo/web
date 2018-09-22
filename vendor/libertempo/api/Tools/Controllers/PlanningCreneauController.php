<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Controllers;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use LibertAPI\Tools\Interfaces;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use \Slim\Interfaces\RouterInterface as IRouter;
use LibertAPI\Planning\Creneau;
use LibertAPI\Tools\Exceptions\UnknownResourceException;

/**
 * Contrôleur des creneaux de plannings
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le Planning\Repository
 */
final class PlanningCreneauController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable, Interfaces\IPostable, Interfaces\IPutable
{
    public function __construct(Creneau\CreneauRepository $repository, IRouter $router)
    {
        parent::__construct($repository, $router);
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        if (!isset($arguments['creneauId'])) {
            return $this->getList($response, (int) $arguments['planningId']);
        }

        return $this->getOne($response, (int) $arguments['creneauId']);
    }

    /**
     * Retourne un élément unique
     *
     * @param IResponse $response Réponse Http
     * @param int $id ID de l'élément
     *
     * @return IResponse, 404 si l'élément n'est pas trouvé, 200 sinon
     */
    private function getOne(IResponse $response, $id)
    {
        try {
            $creneau = $this->repository->getOne($id);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « creneaux#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess(
            $response,
            $this->buildData($creneau),
            200
        );
    }

    /**
     * Retourne un tableau de plannings
     *
     * @param IResponse $response Réponse Http
     * @param int $planningId Contrainte de recherche sur le planning
     *
     * @return IResponse
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    private function getList(IResponse $response, $planningId)
    {
        try {
            $creneaux = $this->repository->getList(['planningId' => $planningId]);
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = array_map([$this, 'buildData'], $creneaux);

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param Creneau\CreneauEntite $entite Créneau de planning
     *
     * @return array
     */
    private function buildData(Creneau\CreneauEntite $entite)
    {
        return [
            'id' => $entite->getId(),
            'planningId' => $entite->getPlanningId(),
            'jourId' => $entite->getJourId(),
            'typeSemaine' => $entite->getTypeSemaine(),
            'typePeriode' => $entite->getTypePeriode(),
            'debut' => $entite->getDebut(),
            'fin' => $entite->getFin(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function post(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        $body = $request->getParsedBody();
        if (null === $body) {
            return $this->getResponseBadRequest($response, 'Body request is not a json content');
        }
        if (is_array($body) && !is_array(reset($body))) {
            return $this->getResponseBadRequest($response, 'Body request is not a creneaux list');
        }
        $planningId = (int) $arguments['planningId'];

        try {
            $creneauxIds = $this->repository->postList($body, new Creneau\CreneauEntite([]));
            $dataMessage = [];
            foreach ($creneauxIds as $id) {
                $dataMessage[] = $this->router->pathFor('getPlanningCreneauDetail', [
                    'creneauId' => $id,
                    'planningId' => $planningId,
                ]);
            }
        } catch (MissingArgumentException $e) {
            return $this->getResponseMissingArgument($response);
        } catch (\DomainException $e) {
            return $this->getResponseBadDomainArgument($response, $e);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess(
            $response,
            $dataMessage,
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

        $id = (int) $arguments['creneauId'];

        try {
            $this->repository->putOne($id, $body);
        } catch (UnknownResourceException $e) {
            return $this->getResponseNotFound($response, 'Element « creneau#' . $id . ' » is not a valid resource');
        } catch (MissingArgumentException $e) {
            return $this->getResponseMissingArgument($response);
        } catch (\DomainException $e) {
            return $this->getResponseBadDomainArgument($response, $e);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess($response, '', 204);
    }
}
