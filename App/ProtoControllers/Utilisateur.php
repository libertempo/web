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
        $params = ['p_login' => $login, 'p_etat' => [\App\Models\Conge::STATUT_DEMANDE]];
        $conge = new \App\ProtoControllers\Conge();

        return $conge->exists($params);
    }
}
