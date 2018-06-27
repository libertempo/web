<?php declare(strict_types = 1);
namespace LibertAPI\JourFerie;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use LibertAPI\Tools\Interfaces;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;

/**
 * Contrôleur de jour férié
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 1.0
 *
 * Ne devrait être contacté que par le routeur
 * Ne devrait contacter que le JourFerieRepository
 */
final class JourFerieController extends \LibertAPI\Tools\Libraries\AController
implements Interfaces\IGetable
{
    /**
     * {@inheritDoc}
     */
    protected function ensureAccessUser(string $order, \LibertAPI\Utilisateur\UtilisateurEntite $utilisateur)
    {
        unset($order);
        if (!$utilisateur->isHautResponsable()) {
            throw new \LibertAPI\Tools\Exceptions\MissingRightException('');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        return $this->getList($request, $response);
    }

    /**
     * Retourne un tableau de jours fériés
     *
     * @param IRequest $request Requête Http
     * @param IResponse $response Réponse Http
     */
    private function getList(IRequest $request, IResponse $response) : IResponse
    {
        try {
            $this->ensureAccessUser(__FUNCTION__, $this->currentUser);
            $jours = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\LibertAPI\Tools\Exceptions\MissingRightException $e) {
            return $this->getResponseForbidden($response, $request);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = array_map([$this, 'buildData'], $jours);

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     */
    private function buildData(JourFerieEntite $entite) : array
    {
        return [
            'date' => $entite->getDate(),
        ];
    }
}
