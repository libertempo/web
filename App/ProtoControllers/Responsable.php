<?php
namespace App\ProtoControllers;

/**
 * ProtoContrôleur d'utilisateur, en attendant la migration vers le MVC REST
 *
 * @since  1.9
 * @author wouldsmina <wouldsmina@tuxfamily.org>
 * @author Prytoegrian <prytoegrian@protonmail.com>
 */
class Responsable
{
    /**
     * Retourne l'id des groupes d'un responsable
     *
     * @param string $resp
     *
     * @return array $ids
     */
    public static function getIdGroupeResp($resp)
    {
        $ids = [];

        $sql = \includes\SQL::singleton();
        $req = 'SELECT gr_gid AS id FROM `conges_groupe_resp` WHERE gr_login =\''.$resp.'\'';
        $res = $sql->query($req);

        while ($data = $res->fetch_array()) {
            $ids[] = (int) $data['id'];
        }

        return $ids;
    }

    /**
     * Retourne l'id des groupes d'un grand responsable
     *
     * @param string $gresp
     *
     * @return array $ids
     */
    public static function getIdGroupeGrandResponsable($gresp)
    {
        $ids=[];
         $sql = \includes\SQL::singleton();
         $req = 'SELECT ggr_gid AS id FROM `conges_groupe_grd_resp` WHERE ggr_login =\''.$gresp.'\'';
         $res = $sql->query($req);

         while ($data = $res->fetch_array()) {
             $ids[] = (int) $data['id'];
         }

         return $ids;
    }

    /**
     * Vérifie si le responsable est absent
     *
     * @param string $resp identifiant du responsable
     *
     * @return bool
     */
    public static function isRespAbsent($resp)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT p_num FROM conges_periode WHERE p_login = "'
                    . \includes\SQL::quote($resp).'" AND p_etat = \''. \App\Models\Conge::STATUT_VALIDATION_FINALE
                    . '\' AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW())
                    AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())
                )';

        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    public static function getLoginGrandResponsableUtilisateur($user)
    {
        $groupesIdUser = \App\ProtoControllers\Utilisateur::getGroupesId($user);

        $grandResp = [];
        $sql = \includes\SQL::singleton();
        $req = 'select ggr_login FROM conges_groupe_grd_resp where ggr_gid  IN (\'' . implode(',', $groupesIdUser) . '\')';
        $res = $sql->query($req);

        while ($data = $res->fetch_array()) {
             $grandResp[] = $data['ggr_login'];
        }
        return $grandResp;
    }

    /**
     * Retourne les responsables d'un employé
     *
     * @param string $user
     * @return array
     */
    public static function getResponsablesUtilisateur($user)
    {
        return \App\ProtoControllers\Groupe\Responsable::getListResponsableByGroupeIds(\App\ProtoControllers\Utilisateur::getGroupesId($user));
    }

    /**
     * Retourne les infos des utilisateurs avec les droits responsables
     *
     * @param array $groupesId
     *
     * @return array
     */
    public static function getInfosResponsables(\includes\SQL $sql, $activeSeul = false)
    {
        $req = 'SELECT *
                FROM conges_users
                WHERE u_is_resp = "Y"';
        if ($activeSeul) {
            $req .= ' AND u_is_active = "Y"';
        }
        $req .= ' ORDER BY u_nom ASC, u_prenom ASC';

        return $sql->query($req)->fetch_all(\MYSQLI_ASSOC);
    }

    /**
     * Vérifie si un utilisateur est bien le responsable d'un employé
     *
     * @param string $resp
     * @param string $user
     *
     * @return bool
     */
    public static function isRespDeUtilisateur($resp, $user)
    {
        return $resp != $user
                && \App\ProtoControllers\Groupe::isResponsableGroupe($resp, \App\ProtoControllers\Utilisateur::getGroupesId($user), \includes\SQL::singleton());
    }

    /**
     * Vérifie si un utilisateur est responsable par délégation d'un employé
     *
     * @param string $resp
     * @param string $user
     *
     * @return boolean
     */
    public static function isRespParDelegation($resp, $user)
    {
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
        if (!$config->isGestionResponsableAbsent()) {
            return false;
        }
        $usersRespRespAbs = [];
        $groupesIdResp = \App\ProtoControllers\Responsable::getIdGroupeResp($resp);
        $usersResp = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupesIdResp);
        foreach ($usersResp as $userResp) {
            if (\App\ProtoControllers\Utilisateur::isResponsable($userResp) && \App\ProtoControllers\Responsable::isRespAbsent($userResp)) {
                $usersRespRespAbs[] = $userResp;
            }
        }
        if (empty($usersRespRespAbs)) {
            return false;
        }

        $RespsUser = \App\ProtoControllers\Responsable::getResponsablesUtilisateur($user);
        $RespUserPresent = array_diff($RespsUser, $usersRespRespAbs);
        if (empty($RespUserPresent)) {
            return true;
        }

        return false;
    }
    /**
     * Vérifie si un utilisateur est bien le grand responsable d'un employé
     *
     * @param string $resp
     * @param array  $groupesId
     *
     * @return bool
     */
    public static function isGrandRespDeGroupe($resp, array $groupesId)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT ggr_gid
                    FROM conges_groupe_grd_resp
                    WHERE ggr_gid IN (\'' . implode(',', $groupesId) . '\')
                        AND ggr_login = "'.\includes\SQL::quote($resp).'"
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    /**
     * Vérifie si le groupe d'un employé est en double validation
     *
     * @param string $user
     *
     * @return bool
     */
    public static function isDoubleValGroupe($user)
    {
        $groupes = [];
        $groupes = \App\ProtoControllers\Utilisateur::getGroupesId($user);
        if (empty($groupes)) {
            return false;
        }

        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT g_double_valid
                    FROM conges_groupe
                    WHERE g_gid ='. $groupes[0] . '
                    AND g_double_valid = "Y"
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }
}
