<?php
namespace App\Components\Utilisateur;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur d'utilisateurs
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.4
 * @see \Tests\Units\App\Components\Utilisateur\Controller
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le Utilisateur\Repository
 */
final class UtilisateurController extends \App\Libraries\AController
{
    /**
     * {@inheritDoc}
     */
    protected function ensureAccessUser($order, LibertAPI\Utilisateur\UtilisateurEntite $utilisateur)
    {
    }

    /*************************************************
     * GET
     *************************************************/

    /* Mettre le niveau de droits autorisés
        && ce que ces droits permettre de voir (l'utilisateur peut-il voir HR / admin ?)
     */

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
        if (!isset($arguments['utilisateurId'])) {
            return $this->getList($request, $response);
        }

        return $this->getOne($response, (int) $arguments['utilisateurId']);
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
            $utilisateur = $this->repository->getOne($id);
        } catch (\DomainException $e) {
            return $this->getResponseNotFound($response, 'Element « utilisateurs#' . $id . ' » is not a valid resource');
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }

        return $this->getResponseSuccess(
            $response,
            $this->buildData($utilisateur),
            200
        );
    }

    /**
     * Retourne un tableau d'utilisateurs
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
            $utilisateurs = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = [];
        foreach ($utilisateurs as $utilisateur) {
            $entites[] = $this->buildData($utilisateur);
        }

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param Entite $entite Utilisateur
     *
     * @return array
     */
    private function buildData(Entite $entite)
    {
        return [
            'id' => $entite->getId(),
            'login' => $entite->getLogin(),
            'nom' => $entite->getNom(),
            'date_inscription' => $entite->getDateInscription(),
            // mettre le lien du planning associé, sous un offset formalisé
        ];
    }
}
