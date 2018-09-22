<?php declare(strict_types = 1);
namespace LibertAPI\Tools\Controllers;

use Psr\Http\Message\ServerRequestInterface as IRequest;
use Psr\Http\Message\ResponseInterface as IResponse;
use \Slim\Interfaces\RouterInterface as IRouter;
use LibertAPI\Journal;

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
    public function __construct(Journal\JournalRepository $repository, IRouter $router)
    {
        parent::__construct($repository, $router);
    }

     /**
      * {@inheritDoc}
      */
    public function get(IRequest $request, IResponse $response, array $arguments) : IResponse
    {
        unset($arguments);
        try {
            $resources = $this->repository->getList(
                $request->getQueryParams()
            );
        } catch (\UnexpectedValueException $e) {
            return $this->getResponseNoContent($response);
        } catch (\Exception $e) {
            return $this->getResponseError($response, $e);
        }
        $entites = array_map([$this, 'buildData'], $resources);

        return $this->getResponseSuccess($response, $entites, 200);
    }

    /**
     * Construit le « data » du json
     *
     * @param Journal\JournalEntite $entite Journal
     *
     * @return array
     */
    private function buildData(Journal\JournalEntite $entite)
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
