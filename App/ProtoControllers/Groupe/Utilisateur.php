<?php
namespace App\ProtoControllers\Groupe;

/**
 * ProtoContrôleur des utilisateurs d'un groupe, en attendant la migration vers le MVC REST
 *
 * @since 1.9
 * @author Prytoegrian <prytoegrian@protonmail.com>
 * @author Wouldsmina
 */
class Utilisateur {
    /*
     * SQL
     */

    /**
     * Retourne le login des membres d'une liste de groupes
     *
     * @param array $groupesId
     *
     * @return array
     */
    public static function getListUtilisateurByGroupeIds(array $groupeIds)
    {
        if (empty($groupeIds)) {
            return [];
        }

        $groupeIds = array_map('intval', $groupeIds);
        $sql = \includes\SQL::singleton();
        $req = 'SELECT gu_login
                FROM `conges_groupe_users`
                WHERE gu_gid IN (' . implode(',', $groupeIds) . ')';
        $res = $sql->query($req);

        $users = [];
        while ($data = $res->fetch_array()) {
            $users[] = $data['gu_login'];
        }

        return $users;
    }
}
