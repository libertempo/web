<?php
namespace LibertAPI\Planning\Creneau;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur des creneaux de plannings
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
final class CreneauController extends \LibertAPI\Tools\Libraries\AController
{
    /**
     * {@inheritDoc}
     */
    protected function ensureAccessUser($order, \LibertAPI\Utilisateur\UtilisateurEntite $utilisateur)
    {
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
     * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
     */
    public function get(IRequest $request, IResponse $response, array $arguments)
    {
        if (!isset($arguments['creneauId'])) {
            return $this->getList($response, (int) $arguments['planningId']);
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
        try {
            $creneau = $this->repository->getOne($id, $planningId);
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
        $entites = [];
        foreach ($creneaux as $creneau) {
            $entites[] = $this->buildData($creneau);
        }

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param CreneauEntite $entite Créneau de planning
     *
     * @return array
     */
    private function buildData(CreneauEntite $entite)
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

    /*************************************************
     * POST
     *************************************************/

     /**
      * Execute l'ordre HTTP POST
      *
      * @param IRequest $request Requête Http
      * @param IResponse $response Réponse Http
      * @param array $arguments Arguments de route
      *
      * @return IResponse
      * @throws \Exception en cas d'erreur inconnue (fallback, ne doit pas arriver)
      */
    public function post(IRequest $request, IResponse $response, array $arguments)
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
            $creneauxIds = $this->repository->postList($body, new CreneauEntite([]));
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
        $planningId = (int) $arguments['planningId'];
        try {
            $creneau = $this->repository->getOne($id, $planningId);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « creneaux#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        try {
            $this->repository->putOne($body, $creneau);
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
