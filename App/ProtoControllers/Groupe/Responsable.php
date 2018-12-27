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
        if (empty($groupeIds)) {
            return [];
        }
        $groupeIds = array_map('intval', $groupeIds);
        $sql = \includes\SQL::singleton();
        $req = 'SELECT gr_login
                FROM conges_groupe_resp
                WHERE gr_gid IN (' . implode(',', $groupeIds) . ')';
        $query = $sql->query($req);

        $respLogin = [];
        while ($data = $query->fetch_array()) {
            $login = $data['gr_login'];
            if (!in_array($login, $respLogin)) {
                $respLogin[] = $login;
            }
        }

        return $respLogin;
    }
}
