<?php declare(strict_types = 1);
namespace LibertAPI\Groupe\GrandResponsable;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use LibertAPI\Tools\Interfaces;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use LibertAPI\Utilisateur\UtilisateurEntite;

/**
 * Contrôleur de grand responsable de groupes
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 * @see \Tests\Units\GroupeController
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le GrandResponsableRepository
 */
final class GrandResponsableController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable
{
    /**
     * {@inheritDoc}
     */
    protected function ensureAccessUser(string $order, UtilisateurEntite $utilisateur)
    {
        unset($order);
        if (!$utilisateur->isAdmin()) {
            throw new \LibertAPI\Tools\Exceptions\MissingRightException('');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        unset($arguments);
        try {
            $this->ensureAccessUser(__FUNCTION__, $this->currentUser);
            $groupes = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\LibertAPI\Tools\Exceptions\MissingRightException $e) {
            return $this->getResponseForbidden($response, $request);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = array_map([$this, 'buildData'], $groupes);

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param UtilisateurEntite $entite Responsable
     *
     * @return array
     */
    private function buildData(UtilisateurEntite $entite)
    {
        return [
            'id' => $entite->getId(),
            'login' => $entite->getLogin(),
            'nom' => $entite->getNom(),
            'prenom' => $entite->getPrenom(),
            'isResp' => $entite->isResponsable(),
            'isAdmin' => $entite->isAdmin(),
            'isHr' => $entite->isHautResponsable(),
            'isActif' => $entite->isActif(),
            'password' => $entite->getMotDePasse(),
            'quotite' => $entite->getQuotite(),
            'email' => $entite->getMail(),
            'numeroExercice' => $entite->getNumeroExercice(),
            'planningId' => $entite->getPlanningId(),
            'heureSolde' => $entite->getHeureSolde(),
            'dateInscription' => $entite->getDateInscription(),
            'dateLastAccess' => $entite->getDateLastAccess(),
        ];
    }
}
