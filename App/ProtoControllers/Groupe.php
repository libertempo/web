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
     * @param array $listeId Ids de groupes à formater en options
     *
     * @return array
     */
    public static function getOptions(array $listeId = [])
    {
        $options = [];
        foreach (static::getListe(\includes\SQL::singleton(), $listeId) as $groupe) {
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
      * Retourne la liste des groupes et les employés associés
      *
      *
      * @return array
      * @todo unescape_string ?
      */
    public static function getListe(\includes\SQL $sql, array $listeId = [])
    {
        $req = 'SELECT *
                FROM conges_groupe CG
                    INNER JOIN conges_groupe_users CGU ON (CG.g_gid = CGU.gu_gid)';
        if (!empty($listeId)) {
            $listeId = array_map('intval', $listeId);
            $req .= ' WHERE CG.g_gid IN (' . implode(',', $listeId) . ')';
        }
        $result = $sql->query($req);

        $groupes = [];
        while ($data = $result->fetch_assoc()) {
            $groupes[] = $data;
        }
        return $groupes;
    }

     /**
      * Retourne la liste des groupes contenant au moins un employé
      *
      *
      * @return array
      * @todo unescape_string ?
      */
    public static function getListeGroupes(\includes\SQL $sql)
    {
        $req = 'SELECT DISTINCT g_gid,g_groupename,g_comment,g_double_valid
                FROM conges_groupe CG
                INNER JOIN conges_groupe_users CGU ON (CG.g_gid = CGU.gu_gid);';
        $result = $sql->query($req);

        $groupes = [];
        while ($data = $result->fetch_assoc()) {
            $groupes[$data['g_gid']] = $data;
        }

        return $groupes;
    }

    /**
     * Retourne les informations d'un groupe
     * 
     * @param int $id
     * @return string
     */
    public static function getInfosGroupe($id, \includes\SQL $sql) 
    {
        $req="SELECT *
              FROM conges_groupe
              WHERE g_gid=". (int) $id;
        $res = $sql->query($req);
        
        $infos = $res->fetch_array();

        if (!empty($infos)){
            $infosGroupe = [
                'nom' => $infos['g_groupename'],
                'doubleValidation' => $infos['g_double_valid'],
                'comment' => $infos['g_comment']
            ];
        } else {
            $infosGroupe = [
                'nom' => '',
                'doubleValidation' => '',
                'comment' => ''
            ];
        }
        
        return $infosGroupe;
    }

    /**
     * Retourne les id de tous les groupes
     *
     * @return array
     */
    public static function getListeId(\includes\SQL $sql)
    {
        $req = 'SELECT g_gid
                FROM conges_groupe';
        $result = $sql->query($req);
        $groupes = [];
        while ($data = $result->fetch_array()) {
            $groupes[] = $data['g_gid'];
        }

        return $groupes;
    }

    /**
     * Verifie si un utilisateur est responsable d'une liste de groupe
     *
     * @param string $resp
     * @param array $groupesId
     *
     * @return bool
     */
    public static function isResponsableGroupe($resp, array $groupesId, \includes\SQL $sql)
    {
        $req = 'SELECT EXISTS (
                    SELECT gr_gid
                    FROM conges_groupe_resp
                    WHERE gr_gid IN (\'' . implode(',', $groupesId) . '\')
                        AND gr_login = "' . $sql->quote($resp) . '"
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }

    /**
     * Verifie si un utilisateur est grand responsable d'une liste de groupe
     *
     * @param string $resp
     * @param array $groupesId
     *
     * @return bool
     */
    public static function isGrandResponsableGroupe($resp, array $groupesId, \includes\SQL $sql)
    {
        $req = 'SELECT EXISTS (
                    SELECT ggr_gid
                    FROM conges_groupe_grd_resp
                    WHERE ggr_gid IN (\'' . implode(',', $groupesId) . '\')
                        AND ggr_login = "' . $sql->quote($resp) . '"
                )';
        $query = $sql->query($req);

        return 0 < (int) $query->fetch_array()[0];
    }
}
