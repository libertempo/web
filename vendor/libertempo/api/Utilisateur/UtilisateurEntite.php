<?php declare(strict_types = 1);
namespace LibertAPI\Utilisateur;

use LibertAPI\Tools\Exceptions\MissingArgumentException;
use LibertAPI\Tools\Helpers\Formatter;

/**
 * @inheritDoc
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 * @see \LibertAPI\Tests\Units\Utilisateur\Entite
 *
 * Ne devrait être contacté que par le UtilisateurRepository et Groupe\Responsable\ResponsableRepository
 * Ne devrait contacter personne
 */
class UtilisateurEntite extends \LibertAPI\Tools\Libraries\AEntite
{
    public function getToken()
    {
        return $this->getFreshData('token');
    }

    public function getLogin()
    {
        return $this->getFreshData('login');
    }

    public function getNom()
    {
        return $this->getFreshData('nom');
    }

    public function getPrenom()
    {
        return $this->getFreshData('prenom');
    }

    /* Est ce vraiment utile d'avoir un tableau pour le stockage ?
        Si non, ça nous permettrait d'avoir une empreinte moindre (N. Popov),
        et supprimer une grande partie des accesseurs
     */

    public function isResponsable() : bool
    {
        return $this->getFreshData('isResp');
    }

    public function isAdmin() : bool
    {
        return $this->getFreshData('isAdmin');
    }

    public function isHautResponsable() : bool
    {
        return $this->getFreshData('isHr');
    }

    public function isActif() : bool
    {
        return $this->getFreshData('isActif');
    }

    public function getMotDePasse()
    {
        return $this->getFreshData('password');
    }

    public function getQuotite()
    {
        return $this->getFreshData('quotite');
    }

    public function getMail()
    {
        return $this->getFreshData('email');
    }

    public function getNumeroExercice()
    {
        return $this->getFreshData('numeroExercice');
    }

    public function getPlanningId()
    {
        return $this->getFreshData('planningId');
    }

    public function getHeureSolde()
    {
        return $this->getFreshData('heureSolde');
    }

    public function getDateInscription()
    {
        return $this->getFreshData('dateInscription');
    }

    public function getDateLastAccess()
    {
        return $this->getFreshData('dateLastAccess');
    }

    /**
     * @inheritDoc
     */
    public function populate(array $data)
    {
        if (!$this->hasAllRequired($data)) {
            throw new MissingArgumentException('');
        }
    }

    /**
     * Insère le token dans l'entité
     *
     * @param string $token Nouveau token d'indentification utilisateur
     *
     * @throws \DomainException Si la donnée n'entre pas dans le domaine de définition, où les erreurs sont jsonEncodée dans le message
     * @example ['nomChamp' => [listeErreurs]]
     */
    public function populateToken($token)
    {
        $this->setToken($token);

        $erreurs = $this->getErreurs();
        if (!empty($erreurs)) {
            throw new \DomainException(json_encode($erreurs));
        }
    }

    /**
     * Tente l'insertion d'une donnée en tant que champ « token »
     *
     * Stocke une erreur si la donnée ne colle pas au domaine
     *
     * @param string $token
     */
    private function setToken($token)
    {
        // domaine de token ?
        if (empty($token)) {
            $this->setErreur('token', 'Le champ est vide');
            return;
        }

        $this->dataUpdated['token'] = $token;
    }

    /**
     * @since 0.3
     */
    public function updateDateLastAccess()
    {
        $this->dataUpdated['dateLastAccess'] = Formatter::timeToSQLDatetime(time());
    }

    /**
     * @since 1.0
     * @TODO 2018-05-26: Supprimer la comparaison md5 quand tous les MDP seront migrés
     */
    public function isPasswordMatching(string $password) : bool
    {
        return password_verify($password, $this->getMotDePasse())
            || $this->getMotDePasse() === md5($password);
    }

    /**
     * @inheritDoc
     * @TODO L'entité utilisateur n'a pas de clé primaire en int, donc on surcharge le parent. Mettre une PK en int !
     */
    final protected function setId($id)
    {
        $this->id = (string) $id;
    }
}
