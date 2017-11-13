<?php
namespace LibertAPI\Tools\Middlewares;

use \LibertAPI\Tools\Libraries\AEntite;
use Psr\Http\Message\ServerRequestInterface as IRequest;
use LibertAPI\Tools\Helpers\Formatter;

/**
 * Identification d'un utilisateur via la transmission du token
 *
 * @since 0.1
 */
final class Identification
{
    /**
     * @var int Durée de validité du token fourni, en secondes
     */
    const DUREE_SESSION = 30*60;
    /**
     * @var AEntite | null
     */
    private $utilisateur;

    public function __construct(IRequest $request, \LibertAPI\Tools\Libraries\ARepository $repository)
    {
        $token = $request->getHeaderLine('Token');
        if (empty($token)) {
            return;
        }
        try {
            $this->utilisateur = $repository->find([
                'token' => $token,
                'gt_date_last_access' => $this->getDateLastAccessAuthorized()
            ]);
        } catch (\UnexpectedValueException $e) {
            return;
        }
    }

    /**
     * Retourne la date limite de dernier accès pour être considéré en ligne
     *
     * @return string
     */
    private function getDateLastAccessAuthorized()
    {
        return Formatter::timeToSQLDatetime(time() - static::DUREE_SESSION);
    }

    /**
     * Vérifie que la clé d'api fournie est la bonne
     *
     * @return bool
     */
    public function isTokenOk()
    {
        return $this->getUtilisateur() instanceof AEntite;
    }

    /**
     * Retourne l'utilisateur courant
     *
     * @return AEntite | null
     * @since 0.3
     */
    public function getUtilisateur()
    {
        return $this->utilisateur;
    }
}
