<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur d'utilisateur, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
class Utilisateur
{
    /*
     * SQL
     */

    public static function getListId()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT u_login
                FROM conges_users';
        $result = $sql->query($req);

        $users = [];
        while ($data = $result->fetch_array()) {
            $users[] = $data['u_login'];
        }

        return $users;
    }

    /**
     * Retourne si un utilisateur a le rôle de de RH
     *
     * @param string $utilisateur
     *
     * @return bool
     * @todo On devrait pouvoir factoriser les isX via getRole() mais actuellement un utilisateur peut avoir plusieurs rôle en simultanée. Il faudra empêcher ça, et ainsi faire pointer les isX() sur getRole(). Et mettre des constantes
     */
    public static function isRH($utilisateur)
    {
        $donneesUtilisateur = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($utilisateur);

        return (!empty($donneesUtilisateur))
            ? $donneesUtilisateur['u_is_hr']
            : false;
    }

    /**
     * Retourne si un utilisateur a le rôle d'admin
     *
     * @param string $utilisateur
     *
     * @return bool
     * @todo On devrait pouvoir factoriser les isX via getRole() mais actuellement un utilisateur peut avoir plusieurs rôle en simultanée. Il faudra empêcher ça, et ainsi faire pointer les isX() sur getRole(). Et mettre des constantes
     */
    public static function isAdmin($utilisateur)
    {
        $donneesUtilisateur = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($utilisateur);

        return (!empty($donneesUtilisateur))
            ? $donneesUtilisateur['u_is_admin']
            : false;
    }

    /**
     * Retourne si un utilisateur a le rôle de responsable
     *
     * @param string $utilisateur
     *
     * @return bool
     * @todo On devrait pouvoir factoriser les isX via getRole() mais actuellement un utilisateur peut avoir plusieurs rôle en simultanée. Il faudra empêcher ça, et ainsi faire pointer les isX() sur getRole(). Et mettre des constantes
     */
    public static function isResponsable($utilisateur)
    {
        $donneesUtilisateur = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($utilisateur);

        return (!empty($donneesUtilisateur))
            ? $donneesUtilisateur['u_is_resp']
            : false;
    }

    /**
     * Retourne les informations d'un utilisateur
     *
     * @param string $login
     *
     * @return string $donnees
     */
    public static function getDonneesUtilisateur($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_users
                WHERE u_login = \''.  \includes\SQL::quote($login).'\'';
        $query = $sql->query($req);
        $donnees = $query->fetch_array();

        return $donnees;
    }

     /**
      * Retourne la liste des utilisateurs associés à un planning
      *
      * @param int $planningId
      *
      * @return array
      */
    public static function getListByPlanning($planningId)
    {
        $planningId = (int) $planningId;
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_users
                WHERE planning_id = ' . $planningId;

        return $sql->query($req)->fetch_all(\MYSQLI_ASSOC);
    }

    /**
     * Retourne les identifiants de groupe auquel un utilisateur appartient
     *
     * @param string $user
     *
     * @return array $ids
     */
    public static function getGroupesId($user)
    {
        $ids = [];
        $sql = \includes\SQL::singleton();
        $req = 'SELECT gu_gid AS id
                    FROM conges_groupe_users
                    WHERE gu_login ="'.\includes\SQL::quote($user).'"';
        $res = $sql->query($req);
        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne le solde de conges (selon le type) d'un utilisateur
     *
     * @param string $login
     * @param int $typeId
     *
     * @return int $solde
     */
    public static function getSoldeconge($login, $typeId)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT su_solde FROM conges_solde_user WHERE su_login = \''.$login.'\'
                AND su_abs_id ='. (int) $typeId;
        $query = $sql->query($req);
        $solde = $query->fetch_array()[0];

        return $solde;
    }

    /**
     * Vérifie si l'utilisateur a des congés en cours
     *
     * @param string $login
     *
     * @return bool
     */
    public static function hasCongesEnCours($login)
    {
        $params = ['p_login' => $login, 'p_etat' => \App\Models\Conge::STATUT_DEMANDE];
        $conge = new \App\ProtoControllers\Employe\Conge();

        return $conge->exists($params);
    }

    /**
     * Vérifie si l'utilisateur a des heures de repos en cours
     *
     * @param string $login
     *
     * @return bool
     */
    public static function hasHeureReposEnCours($login)
    {
        $params = ['login' => $login, 'statut' => \App\Models\Heure\Repos::STATUT_DEMANDE];
        $repos = new \App\ProtoControllers\Employe\Heure\Repos();

        return $repos->exists($params);
    }

    /**
     * Vérifie si l'utilisateur a des heures additionnelles en cours
     *
     * @param string $login
     *
     * @return bool
     */
    public static function hasHeureAdditionnelleEnCours($login)
    {
        $params = ['login' => $login, 'statut' => \App\Models\Heure\Additionnelle::STATUT_DEMANDE];
        $additionnelle = new \App\ProtoControllers\Employe\Heure\Additionnelle();

        return $additionnelle->exists($params);
    }
}
