<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur de groupe, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author wouldsmina
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Groupe
{
    /**
     * Retourne les options de select des groupes
     *
     * @return array
     */
    public static function getOptions()
    {
        $options = [];
        foreach (static::getListe() as $groupe) {
            if (!isset($options[$groupe['g_gid']])) {
                $options[$groupe['g_gid']] = [
                    'nom' => $groupe['g_groupename'],
                ];
            }
            $options[$groupe['g_gid']]['utilisateurs'][] = $groupe['gu_login'];
        }

        return $options;
    }

    /*
     * SQL
     */

     /**
      * Retourne la liste des groupes de l'application
      *
      * Il est fort probable que cette méthode change de portée. Pour le moment c'est pas utile
      *
      * @return array
      * @todo unescape_string ?
      */
    private static function getListe()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_groupe CG
                    INNER JOIN conges_groupe_users CGU ON (CG.g_gid = CGU.gu_gid)';
        $result = $sql->query($req);

        $groupes = [];
        while ($data = $result->fetch_assoc()) {
            $groupes[] = $data;
        }

        return $groupes;
    }

    /**
     * Retourne les id de tous les groupes
     *
     * @return array
     */
    public static function getListeId()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT g_gid
                FROM conges_groupe';
        $result = $sql->query($req);

        $groupes = [];
        while ($data = $result->fetch_array()) {
            $groupes[] = $data['g_gid'];
        }

        return $groupes;
    }
}
