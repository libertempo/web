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
            $options[$groupe['g_gid']] = $groupe['g_groupename'];
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
                FROM conges_groupe';
        $result = $sql->query($req);

        $groupes = [];
        while ($data = $result->fetch_array()) {
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
