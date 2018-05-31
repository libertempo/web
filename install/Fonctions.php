<?php
namespace install;

/**
 * Regroupement de fonctions d'installation
 */
class Fonctions
{
    public static function getInstalledVersion() : string
    {
        $db = \includes\SQL::singleton();
        try {
            $reglog = $db->query('show tables like \'conges_config\';');
            if ($reglog->num_rows == 0) {
                return 0;
            }

            $sql="SELECT conf_valeur FROM conges_config WHERE conf_nom='installed_version' ";
            if ($reglog = $db->query($sql) && $result = $reglog->fetch_array()) {
                return $result['conf_valeur'];
            }
        } catch (\Exception $e) {
            return 0;
        }
        return 0;
    }

}
