<?php
namespace admin;

/**
* Regroupement des fonctions liées à l'admin
*/
class Fonctions
{
// recup des data d'une table sous forme de INSERT ...
    public static function get_table_data($table)
    {

        $chaine_data="";

        // suppression des donnéées de la table :
        $chaine_delete='DELETE FROM `'. \includes\SQL::quote($table).'` ;'."\n";
        $chaine_data=$chaine_data.$chaine_delete ;

        // recup des donnéées de la table :
        $sql_data='SELECT * FROM '. \includes\SQL::quote($table);
        $ReqLog_data = \includes\SQL::query($sql_data);

        while ($resultat_data = $ReqLog_data->fetch_array()) {
            $count_fields=count($resultat_data)/2;   // on divise par 2 car c'est un tableau indexé (donc compte key+valeur)
            $chaine_insert = "INSERT INTO `$table` VALUES ( ";
            for($i=0; $i<$count_fields; $i++)
            {
                if(isset($resultat_data[$i]))
                    $chaine_insert = $chaine_insert."'".addslashes($resultat_data[$i])."'";
                else
                    $chaine_insert = $chaine_insert."NULL";

                if($i!=$count_fields-1)
                    $chaine_insert = $chaine_insert.", ";
            }
            $chaine_insert = $chaine_insert." );\n";

            $chaine_data=$chaine_data.$chaine_insert;
        }

        return $chaine_data;
    }

    // recup de la structure d'une table sous forme de CREATE ...
    public static function get_table_structure($table)
    {
        $drop = "DROP TABLE IF EXISTS  `$table` ;\n";

        // description des champs :
        $req = 'SHOW CREATE TABLE '. \includes\SQL::quote($table);
        $descriptor = \includes\SQL::query($req) ;
        $resultDescriptor = $descriptor->fetch_array();
        return $drop . $resultDescriptor['Create Table'] . ";\n#\n";
    }

    public static function restaure($fichier_restaure_name, $fichier_restaure_tmpname, $fichier_restaure_size, $fichier_restaure_error)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        header_popup();

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        if( ($fichier_restaure_error!=0)||($fichier_restaure_size==0) ) // s'il y a eu une erreur dans le telechargement OU taille==0
            //(cf code erreur dans fichier features.file-upload.errors.html de la doc php)
        {
            //message d'erreur et renvoit sur la page précédente (choix fichier)
            $return.= '<form action="' . $PHP_SELF . '" method="POST">';
            $table = new \App\Libraries\Structure\Table();
            $childTable = '<tr>';
            $childTable .= '<th>' . _('admin_sauve_db_bad_file') . ' : <br>' . $fichier_restaure_name .'</th>';
            $childTable .= '</tr><tr>';
            $childTable .= '<td align="center">';
            $childTable .= '<input type="hidden" name="choix_action" value="restaure">';
            $childTable .= '<input type="submit" value="' . _('form_redo') . '">';
            $childTable .= '</td>';
            $childTable .= '</tr><tr>';
            $childTable .= '<td align="center">';
            $childTable .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
            $childTable .= '</td>';
            $childTable .= '</tr>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
            $return .= '</form>';
        } else {
            $result = execute_sql_file($fichier_restaure_tmpname);

            $return .= '<form action="" method="POST">';
            $table = new \App\Libraries\Structure\Table();
            $childTable = '<tr>';
            $childTable .= '<th>' . _('admin_sauve_db_restaure_ok') . ' !</th>';
            $childTable .= '</tr>';
            $childTable .= '<tr>';
            $childTable .= '<td align="center">&nbsp;</td>';
            $childTable .= '</tr>';
            $childTable .= '<tr>';
            $childTable .= '<td>';
            $childTable .= '</td>';
            $childTable .= '</tr>';
            $table->addChild($childTable);
            ob_start();
            $table->render();
            $return .= ob_get_clean();
            $return .= '</form>';
        }
        echo $return;
    }

    // RESTAURATION
    public static function choix_restaure()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';
        $return .= '<form enctype="multipart/form-data" action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th>' . _('admin_sauve_db_restaure') . '<br>' . _('admin_sauve_db_file_to_restore') . ' :</th>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center"><input type="file" name="fichier_restaure"></td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center">&nbsp;</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center"><font color="red">' . _('admin_sauve_db_warning') . ' !</font></td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center">&nbsp;</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center">';
        $childTable .= '<input type="hidden" name="choix_action" value="restaure">';
        $childTable .= '<input type="submit" value="' . _('admin_sauve_db_do_restaure') . '">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td align="center">';
        $childTable .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '</form>';

        echo $return;
    }

    public static function commit_sauvegarde($type_sauvegarde)
    {
        header("Pragma: no-cache");
        header("Content-Type: text/x-delimtext; name=\"php_conges_".$type_sauvegarde.".sql\"");
        header("Content-disposition: attachment; filename=php_conges_".$type_sauvegarde.".sql");

        echo static::getDataFile($type_sauvegarde);
    }

    /**
     * Écrit un fichier de sauvegarde de version dans le répertoire de backup
     *
     * @param string $previousVersion Version de départ (courante)
     * @param string $newVersion Version visée
     *
     * @throws \Exception en cas d'échec d'écriture de fichier
     */
    public static function sauvegardeAsFile($previousVersion, $newVersion)
    {
        $typeSauvegarde = 'all';
        $contentFile = static::getDataFile($typeSauvegarde);
        $filename = BACKUP_PATH . 'libertempo_' . $typeSauvegarde .'_' . $previousVersion . '__' . $newVersion . '.sql'; // nom de migration

        if (false === file_put_contents($filename, $contentFile)) {
            throw new \Exception('Échec de l\'écriture de la sauvegarde');
        }
    }

    /**
     * Retourne les données de sauvegarde
     *
     * @param string $typeSauvegarde Si on veut sauvegarder la structure seule ou les données
     *
     * @return string
     */
    public static function getDataFile($typeSauvegarde)
    {
        $content = "#\n";
        $content .= "# Libertempo\n";
        $content .= "#\n# DATE : " . date("d-m-Y H:i:s") . "\n";
        $content .= "#\n";

        //recup de la liste des tables
        $ReqLog = \includes\SQL::query('SHOW TABLES');
        while ($resultat = $ReqLog->fetch_array()) {
            $table = $resultat[0];
            $content .= "#\n#\n# TABLE: $table \n#\n";
            if(($typeSauvegarde=="all") || ($typeSauvegarde=="structure") ) {
                $content .= "# Struture : \n#\n";
                $content .= static::get_table_structure($table);
            }
            if(($typeSauvegarde=="all") || ($typeSauvegarde=="data") ) {
                $content .= "# Data : \n#\n";
                $content .= static::get_table_data($table);
            }
        }

        return $content;
    }

    public static function sauve($type_sauvegarde)
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        redirect(ROOT_PATH .'admin/admin_db_sauve.php?choix_action=sauvegarde&type_sauvegarde='.$type_sauvegarde.'&commit=ok', false);

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th colspan="2">' . _('admin_sauve_db_save_ok') . ' ...</th>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '</form>';

        echo $return;
    }

    // SAUVEGARDE
    public static function choix_sauvegarde()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th colspan="2">' . _('admin_sauve_db_options') . '</th>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td><input type="radio" name="type_sauvegarde" value="all" checked></td>';
        $childTable .= '<td>' . _('admin_sauve_db_complete') . '</td>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td><input type="radio" name="type_sauvegarde" value="data"></td>';
        $childTable .= '<td>' . _('admin_sauve_db_data_only') . '</td>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '&nbsp;';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '<input type="hidden" name="choix_action" value="sauvegarde">';
        $childTable .= '<input type="submit" value="' . _('admin_sauve_db_do_sauve') . '">';
        $childTable .= '</td>';
        $childTable .= '</tr><tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '</form>';

        echo $return;
    }

    // CHOIX
    public static function choix_save_restore()
    {
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        $return   = '';

        $return .= '<h1>' . _('admin_sauve_db_titre') . '</h1>';

        $return .= '<form action="' . $PHP_SELF . '" method="POST">';
        $table = new \App\Libraries\Structure\Table();
        $childTable = '<tr>';
        $childTable .= '<th colspan="2">' . _('admin_sauve_db_choisissez') . ' :</th>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td><input type="radio" name="choix_action" value="sauvegarde" checked></td>';
        $childTable .= '<td><b>' . _('admin_sauve_db_sauve') . '</b></td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td><input type="radio" name="choix_action" value="restaure" /></td>';
        $childTable .= '<td><b>' . _('admin_sauve_db_restaure') . '</b></td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '&nbsp;';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td colspan=2" align="center">';
        $childTable .= '<input type="submit" value="' . _('form_submit') . '">';
        $childTable .= '</td>';
        $childTable .= '</tr>';
        $childTable .= '<tr>';
        $childTable .= '<td colspan="2" align="center">';
        $childTable .= '<input type="button" value="' . _('form_cancel') . '" onClick="window.close();">';
        $childTable .= '</td></tr>';
        $table->addChild($childTable);
        ob_start();
        $table->render();
        $return .= ob_get_clean();
        $return .= '</form>';
        echo $return;
    }

    /**
     * Encapsule le comportement du module de sauvegarde / restauration de bdd
     *
     * @return void
     * @access public
     * @static
     */
    public static function saveRestoreModule()
    {
        /*** initialisation des variables ***/
        /*************************************/
        // recup des parametres reçus :
        // SERVER
        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        // GET / POST
        $choix_action    = getpost_variable('choix_action');
        $type_sauvegarde = getpost_variable('type_sauvegarde');
        $commit          = getpost_variable('commit');

        $fichier_restaure_name="";
        $fichier_restaure_tmpname="";
        $fichier_restaure_size=0;
        $fichier_restaure_error=4;
        if(isset($_FILES['fichier_restaure'])) {
            $fichier_restaure_name=$_FILES['fichier_restaure']['name'];
            $fichier_restaure_size=$_FILES['fichier_restaure']['size'];
            $fichier_restaure_tmpname=$_FILES['fichier_restaure']['tmp_name'];
            $fichier_restaure_error=$_FILES['fichier_restaure']['error'];
        }
        /*************************************/
        if($choix_action=="") {
            \admin\Fonctions::choix_save_restore();
        } elseif($choix_action=="sauvegarde") {
            if(!isset($type_sauvegarde) || $type_sauvegarde=="") {
                \admin\Fonctions::choix_sauvegarde();
            } else {
                if( (!isset($commit)) || ($commit=="")) {
                    \admin\Fonctions::sauve($type_sauvegarde);
                } else {
                    \admin\Fonctions::commit_sauvegarde($type_sauvegarde);
                }
            }
        } elseif($choix_action=="restaure") {
            if( (!isset($fichier_restaure_name)) || ($fichier_restaure_name=="")||(!isset($fichier_restaure_tmpname)) || ($fichier_restaure_tmpname=="") )
                \admin\Fonctions::choix_restaure();
            else
                \admin\Fonctions::restaure($fichier_restaure_name, $fichier_restaure_tmpname, $fichier_restaure_size, $fichier_restaure_error);
        } else {
            /* APPEL D'UNE AUTRE PAGE immediat */
            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=admin_index.php?onglet=admin-users\">";
        }
    }
}
