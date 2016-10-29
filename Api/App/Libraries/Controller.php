<?php
namespace Api\App\Libraries;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use \Api\App\Libraries\Repository;

/**
 * Contrôleur principal
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.1
 *
 * Ne devrait être contacté par personne
 * Ne devrait contacter personne
 */
abstract class Controller
{
    /**
     * @var ServerRequestInterface Requête HTTP
     */
    protected $request;

    /**
     * @var ResponseInterface Réponse HTTP
     */
    protected $response;

    /**
     * @var Repository Repository de la ressource
     */
    protected $repository;

    /**
     * @var Repository Repository de l'utilisateur
     */
    private $utilisateurRepository;

    public function __construct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        Repository $repository,
        Repository $utilisateurRepository
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->repository = $repository;
        $this->utilisateurRepository = $utilisateurRepository;

        if (!$this->isApiKeyOk()) {
            // Preciser la bonne exception
            throw new \DomainException("Error Processing Request", 1);
            return $this->getResponseErrorAuthentication();
        }
        //$this->utilisateur = $utilisateurRepository->get();// iduser
        if (!$this->isResourceXXXForUser()) {
            // Preciser la bonne exception
            throw new \LogicException("Error Processing Request", 1);

            return $this->getResponseErrorAccess();
        }
        //$this->checkApiKey();
        // getUserById() dans le repository associé et set dans le contrôleur
    }

    /**
     * Vérifie que la clé d'api fournie est la bonne
     *
     * @return bool
     */
    private function isApiKeyOk()
    {
        //oauth ?
        return true;
    }

    /**
     * Retourne une réponse d'erreur 401
     *
     * @return string JSON bien formé
     */
    private function getResponseErrorAuthentication()
    {

    }

    /**
     * Vérifie que la ressource est accessible pour l'utilisateur courant
     *
     * @return bool
     */
    private function isResourceXXXForUser()
    {
        //$utilisateur = $this->utilisateurRepository->get();
        // qu'est ce que ça veut dire qu'une ressource est accessible, et où le mettre ? dépend du rôle ?

        return true;
    }

    /**
     * Retourne les méthodes HTTP disponibles au sens de la ressource
     *
     * @return string
     */
    abstract public function getAvailablesMethods();

    /**
     * Retourne le nom de la ressource (au pluriel)
     *
     * @return string
     */
    abstract public function getResourceName();
}
