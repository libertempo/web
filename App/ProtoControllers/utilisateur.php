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
     * retourne les identifiants de groupe auquel un utilisateur appartient
     * 
     * @param string $user
     * 
     * @return array $ids
     */
    public static function getGroupesId($user)
    {
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
}

