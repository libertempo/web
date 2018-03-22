<?php
namespace LibertAPI\Journal;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur de journal
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.5
 * @see \LibertAPI\Tests\Units\Journal\JournalController
 */
final class JournalController extends \LibertAPI\Tools\Libraries\AController
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
        unset($arguments);
        try {
            $resources = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\Exception $e) {
            throw $e;
        }

        $entites = [];
        foreach ($resources as $resource) {
            $entites[] = $this->buildData($resource);
        }

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param JournalEntite $entite Journal
     *
     * @return array
     */
    private function buildData(JournalEntite $entite)
    {
        return [
            'id' => $entite->getId(),
            'numeroPeriode' => $entite->getNumeroPeriode(),
            'utilisateurActeur' => $entite->getUtilisateurActeur(),
            'utilisateurObjet' => $entite->getUtilisateurObjet(),
            'etat' => $entite->getEtat(),
            'commentaire' => $entite->getCommentaire(),
            'date' => $entite->getDate(),
        ];
    }

}
