<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Controllers;

use LibertAPI\Tools\Interfaces;
use LibertAPI\Tools\Exceptions\MissingArgumentException;
use LibertAPI\Tools\Exceptions\UnknownResourceException;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use \Slim\Interfaces\RouterInterface as IRouter;
use LibertAPI\Absence\Type;

/**
 * Contrôleur de type d'absence
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 */
final class AbsenceTypeController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable, Interfaces\IPostable, Interfaces\IPutable, Interfaces\IDeletable
{
    public function __construct(Type\TypeRepository $repository, IRouter $router)
    {
        parent::__construct($repository, $router);
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        if (!isset($arguments['typeId'])) {
            return $this->getList($request, $response);
        }
        return $this->getOne($response, (int) $arguments['typeId']);
    }

    /**
     * Retourne un élément unique
     *
     * @param IResponse $response Réponse Http
     * @param int $id ID de l'élément
     *
     * @return IResponse, 404 si l'élément n'est pas trouvé, 200 sinon
     */
    private function getOne(IResponse $response, int $id) : IResponse
    {
        try {
            $responseResource = $this->repository->getOne($id);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « type#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess(
            $response,
            $this->buildData($responseResource),
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
            $responseResources = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = array_map([$this, 'buildData'], $responseResources);

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param Type\TypeEntite $entite Type
     *
     * @return array
     */
    private function buildData(Type\TypeEntite $entite)
    {
        return [
            'id' => $entite->getId(),
            'type' => $entite->getType(),
            'libelle' => $entite->getLibelle(),
            'libelleCourt' => $entite->getLibelleCourt(),
            'typeNatif' => $entite->isTypeNatif(),
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
            $typeId = $this->repository->postOne($body);
        } catch (MissingArgumentException $e) {
            return $this->getResponseMissingArgument($response);
        } catch (\DomainException $e) {
            return $this->getResponseBadDomainArgument($response, $e);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess(
            $response,
            $this->router->pathFor('getAbsenceTypeDetail', [
                'typeId' => $typeId
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

        $id = (int) $arguments['typeId'];
        try {
            $this->repository->putOne($id, $body);
        } catch (UnknownResourceException $e) {
            return $this->getResponseNotFound($response, 'Element « type#' . $id . ' » is not a valid resource');
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
        $id = (int) $arguments['typeId'];
        try {
            $this->repository->deleteOne($id);
        } catch (UnknownResourceException $e) {
            return $this->getResponseNotFound($response, 'Element « type#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess($response, '', 200);
    }
}
