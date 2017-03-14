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
      *
      * @return array
      * @todo unescape_string ?
      */
    public static function getListe()
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT *
                FROM conges_groupe CG
                    INNER JOIN conges_groupe_users CGU ON (CG.g_gid = CGU.gu_gid)';
        $result = $sql->query($req);

        $groupes = [];
        while ($data = $result->fetch_assoc()) {
            $groupes[$data['g_gid']] = $data;
        }

        return $groupes;
    }

    /**
     * Retourne le nom d'un groupe
     * 
     * @param int $id
     * @return string
     */
    public static function getInfosGroupe($id) 
    {
        $sql = \includes\SQL::singleton();
        $req="SELECT *
              FROM conges_groupe
              WHERE g_gid=". $id;
        $res = $sql->query($req);
        
        $infos = $res->fetch_array();
            $infosGroupe = [
                'nom' => $infos['g_groupename'],
                'doubleValidation' => ($infos['g_double_valid'] =="Y")?true:false,
                'comment' => $infos['g_comment']
            ];
        
        return $infosGroupe;
    }
    
    public function isDoubleValidation($id) {
        $sql = \includes\SQL::singleton();
        $req="SELECT g_double_valid
              FROM conges_groupe
              WHERE g_gid=". $id;
        $res = $sql->query($req);
        
        return ($res->fetch_array()['g_double_valid'] =="Y")?true:false;
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

    /**
     * Verifie si un utilisateur est responsable d'une liste de groupe
     *
     * @param string $resp
     * @param array $groupesId
     *
     * @return bool
     */
    public static function isResponsableGroupe($resp, array $groupesId)
    {
        $sql = \includes\SQL::singleton();
        $req = 'SELECT EXISTS (
                    SELECT gr_gid
                    FROM conges_groupe_resp
                    WHERE gr_gid IN (\'' . implode(',', $groupesId) . '\')
                        AND gr_login = "'.\includes\SQL::quote($resp).'"
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
    public static function isGrandResponsableGroupe($resp, array $groupesId)
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
}
