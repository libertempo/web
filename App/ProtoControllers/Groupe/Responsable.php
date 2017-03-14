<?php
namespace App\ProtoControllers\Groupe;

/**
 * ProtoContrôleur des responsables d'un groupe, en attendant la migration vers le MVC REST
 *
 * @since 1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina <wouldsmina@tuxfamily.org>
 */
class Responsable {
    /*
     * SQL
     */

    /**
     * Retourne le login des responsables d'une liste de groupes
     *
     * @param array $groupesId
     *
     * @return array
     */
    public static function getListResponsableByGroupeIds(array $groupeIds)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT gr_login,gr_gid
                FROM conges_groupe_resp
                WHERE gr_gid IN (\'' . implode(',', $groupeIds) . '\')';
        $query = $sql->query($req);

        $respLogin = [];
        while ($data = $query->fetch_array()) {
            $respLogin[$data['gr_gid']] = $data['gr_login'];
        }

        return $respLogin;
    }
}
