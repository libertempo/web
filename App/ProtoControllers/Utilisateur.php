<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur d'utilisateur, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Utilisateur
{
    /*
     * SQL
     */

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

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Récupère la totalité des données d'un utilisateur
     *
     * @param string $login
     *
     * @return array
     */
    public static function getDataByLogin($login)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_users
                WHERE u_login = "' . $sql->quote($login) . '"';

        return $sql->query($req)->fetch_all(MYSQLI_ASSOC)[0];
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
