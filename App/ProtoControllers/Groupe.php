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
    /*
     * SQL
     */

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
