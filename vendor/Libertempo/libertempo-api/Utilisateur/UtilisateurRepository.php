<?php
namespace LibertAPI\Utilisateur;

use LibertAPI\Tools\Libraries\AEntite;
use LibertAPI\Tools\Libraries\Application;

/**
 * {@inheritDoc}
 *
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 *
 * @since 0.2
 * @see \LibertAPI\Tests\Units\Utilisateur\UtilisateurRepository
 */
class UtilisateurRepository extends \LibertAPI\Tools\Libraries\ARepository
{
    /**
     * @var Application Bibliothèque d'accès aux données de l'application
     */
    private $application;

    /**
     * Ajoute la bibliothèque d'accès aux données, pour la génération du token
     *
     * @param Application $application
     */
    public function setApplication(Application $application)
    {
        if ($this->application instanceof Application) {
            throw new \LogicException('Application can\'t be set twice');
        }
        $this->application = $application;
    }

    /*************************************************
     * GET
     *************************************************/

    /**
     * Retourne une ressource correspondant à des critères
     *
     * @param array $parametres
     * @example [offset => 4, start-after => 23, filter => 'name::chapo|status::1,3']
     *
     * @return AEntite
     */
    public function find(array $parametres)
    {
        $list = $this->getList($parametres);
        return reset($list);
    }

    /**
     * @inheritDoc
     */
    final protected function getParamsConsumer2Dao(array $paramsConsumer)
    {
        $results = [];
        if (!empty($paramsConsumer['login'])) {
            $results['u_login'] = (string) $paramsConsumer['login'];
        }
        if (!empty($paramsConsumer['password'])) {
            $results['u_passwd'] = md5($paramsConsumer['password']);
        }
        if (!empty($paramsConsumer['token'])) {
            $results['token'] = (string) $paramsConsumer['token'];
        }
        if (!empty($paramsConsumer['gt_date_last_access'])) {
            $results['gt_date_last_access'] = (string) $paramsConsumer['gt_date_last_access'];
        }
        if (!empty($paramsConsumer['isActif'])) {
            $results['is_active'] = $paramsConsumer['isActif'];
        }
        return $results;
    }

    /*************************************************
     * POST
     *************************************************/

    public function postOne(array $data, AEntite $entite)
    {
    }

    /*************************************************
     * PUT
     *************************************************/

    public function putOne(array $data, AEntite $entite)
    {
    }

    /**
     * « Ping » la date de dernier accès de l'utilisateur
     *
     * @param UtilisateurEntite $entite Entité utilisateur
     *
     * @since 0.3
     */
    public function updateDateLastAccess(UtilisateurEntite $entite)
    {
        $entite->updateDateLastAccess();
        $this->dao->put($entite);
    }

    /**
     * Regénère le token de l'utilisateur pour une nouvelle session
     *
     * @param AEntite $entite Entité utilisateur
     *
     * @return AEntite L'entité hydratée du nouveau token
     * @throws \RuntimeException Si le token instance n'est pas posé
     */
    public function regenerateToken(AEntite $entite)
    {
        $instanceToken = $this->application->getTokenInstance();
        if (empty($instanceToken)) {
            throw new \RuntimeException('Instance token is not set');
        }

        try {
            $entite->populateToken($this->buildToken($instanceToken));
            $entite->updateDateLastAccess();
            $this->dao->put($entite);

            return $entite;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Génère le token
     *
     * @param string $instanceToken Clé API de l'instance
     *
     * @return string
     */
    private function buildToken($instanceToken)
    {
        return password_hash($instanceToken, \PASSWORD_BCRYPT);
    }

    /*************************************************
     * DELETE
     *************************************************/

    public function deleteOne(AEntite $entite)
    {
    }
}
