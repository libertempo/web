<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les 
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE, 
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation 
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps 
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation, 
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either 
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/
namespace install;

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/**
 * Regroupement de fonctions d'installation
 */
class Fonctions {
    // teste le fichier config.php 
    //renvoit TRUE si ok, et FALSE sinon
    public static function test_config_file() {
        return is_readable( CONFIG_PATH .'config.php' );
    }


    // teste le fichier dbconnect.php 
    //renvoit TRUE si ok, et FALSE sinon
    public static function test_dbconnect_file() {
        return is_readable( CONFIG_PATH .'dbconnect.php' ) ;
    }


    // teste l'ancien fichier de conf config_old.php // mis par le user pour upgrade v1.0 to v1.1
    //renvoit TRUE si ok, et FALSE sinon
    public static function test_old_config_file() {
        return is_readable('config_old.php');
    }


    // teste l'existance et la conexion à la database
    //renvoit TRUE si ok, et FALSE sinon
    public static function test_database() {
        try {
            \includes\SQL::singleton();
        }
        catch (Exception $e){
            return false;
        }
        return \includes\SQL::getVar('connect_errno') == 0 ;
    }



    // renvoit le num de la version installée ou 0 s'il est inaccessible (non renseigné ou table non présente) 
    public static function get_installed_version() {
        try {
            $reglog = \includes\SQL::query('show tables like \'conges_config\';');
            if( $reglog->num_rows == 0)
                return 0;
            $sql="SELECT conf_valeur FROM conges_config WHERE conf_nom='installed_version' ";
            if($reglog = \includes\SQL::query($sql))
                if($result=$reglog->fetch_array())
                    return $result['conf_valeur'];
        }
        catch(Exception $e) {
            return 0;
        }
        return 0;
    }



    // teste la creation de table (verif si le user a les droits suffisants ou pas)
    // renvoit TRUE ou FALSE
    public static function test_create_table() {
        /*********************************************/
        // creation de la table `conges_test`
        $sql_create="CREATE TABLE IF NOT EXISTS `conges_test` (
            `test1` varchar(100) BINARY NOT NULL default '',
            `test2` varchar(100) BINARY NOT NULL default '',
            PRIMARY KEY  (`test1`)
                ) ;";
        return \includes\SQL::query($sql_create);
    }


    // teste "alter table" (verif si le user a les droits suffisants ou pas)
    // renvoit TRUE ou FALSE
    public static function test_alter_table() {
        /*********************************************/
        // alter de la table `conges_test`
        $sql_alter="ALTER TABLE `conges_test` CHANGE `test2` `test2` varchar(150) ;" ;
        return \includes\SQL::query($sql_alter) ;
    }


    // teste la suppression de table (verif si le user a les droits suffisants ou pas)
    // renvoit TRUE ou FALSE
    public static function test_drop_table() {
        /*********************************************/
        // suppression de la table `conges_test`
        $sql_drop="DROP TABLE `conges_test` ;" ;
        return \includes\SQL::query($sql_drop);
    }

    public static function write_db_config($server,$user,$passwd,$db){
        if (is_writable( CONFIG_PATH .'dbconnect_new.php' ))
        {
            $newdbconnect = file_get_contents(CONFIG_PATH .'dbconnect_new.php');
            $newdbconnect .= "\n".'$mysql_serveur="'.$server.'" ;'."\n".'$mysql_user="'.$user.'" ;'."\n".'$mysql_pass="'.$passwd.'" ;'."\n".'$mysql_database="'.$db."\" ;\n";
            file_put_contents(CONFIG_PATH .'dbconnect.php', $newdbconnect);
            return true;
        }
        else
        {
            return false;
        }
    }

    // cette fonction verif si une version à déja été installée ou non....
    // elle lance une creation/initialisation de la base
    // ou une migration des version antérieures ....
    public static function install($lang,  $DEBUG=FALSE)
    {
        // soit, c'est une install complète , soit c'est une mise à jour d'une version non déterminée

        header_popup('PHP_CONGES : Installation');

        // affichage du titre
        echo "<center>\n";
        echo "<br><H1><img src=\"". TEMPLATE_PATH ."img/tux_config_32x32.png\" width=\"32\" height=\"32\" border=\"0\" title=\"". _('install_install_phpconges') ."\" alt=\"". _('install_install_phpconges') ."\"> ". _('install_index_titre') ."</H1>\n";
        echo "<br><br>\n";

        echo "<table border=\"0\">\n";
        echo "<tr align=\"center\">\n";
        echo "<td colspan=\"3\"><h2>". _('install_no_prev_version_found') .".<br>". _('install_indiquez') ." ...</h2><br><br></td>\n";
        echo "</tr>\n";
        echo "<tr align=\"center\">\n";
        echo "<td valign=top>\n";
        echo "\n";
        echo "<h3>... ". _('install_nouvelle_install') ."</h3>\n";
        echo "<br>\n";

        // Formulaire : lance install.php
        echo "<form action=\"install.php\" method=\"POST\">\n";
        echo "<input type=\"hidden\" name=\"lang\" value=\"$lang\">\n";
        echo "<input type=\"submit\" value=\"". _('form_start') ."\">\n";
        echo "</form>\n";
        echo "</td>\n";
        echo "<td><img src=\"". TEMPLATE_PATH ."img/shim.gif\" width=\"100\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
        echo "<td valign=top>\n";
        echo "<h3>... ". _('install_mise_a_jour') ."</h3><b>". _('install_indiquez_pre_version') ." :</b><br><br>\n";

        // Formulaire : lance mise_a_jour.php
        echo "<form action=\"mise_a_jour.php\" method=\"POST\">\n";
        // affichage de la liste des versions ...
        echo "<select name=\"version\">\n";
        echo "<option value=\"0\">". _('install_installed_version') ."</option>\n";
        echo "<option value=\"1.6.0\">v1.6.x</option>\n";
        echo "<option value=\"1.5.1\">v1.5.x</option>\n";
        echo "<option value=\"1.4.2\">v1.4.x</option>\n";
        echo "<option value=\"1.4.0\">v1.4.0</option>\n";
        echo "</select>\n";
        echo "<br>\n";
        echo "<input type=\"hidden\" name=\"lang\" value=\"$lang\">\n";
        echo "<input type=\"submit\" value=\"". _('form_start') ."\">\n";
        echo "</form>\n";
        echo "</td>\n";
        echo "</tr>\n";
        echo "</table>\n";

        bottom();
    }

    // install la nouvelle version dans une database vide ... et config
    public static function lance_install($lang, $DEBUG=FALSE)
    {

        $PHP_SELF=$_SERVER['PHP_SELF'];

        include CONFIG_PATH .'dbconnect.php' ;
        include ROOT_PATH .'version.php' ;

        //verif si create / alter table possible !!!
        if(!\install\Fonctions::test_create_table($DEBUG))
        {
            echo "<font color=\"red\"><b>CREATE TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
            echo "<br>". _('install_puis') ." ...<br>\n";
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
            echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
            echo "</form>\n";
        }
        elseif(!\install\Fonctions::test_drop_table($DEBUG))
        {
            echo "<font color=\"red\"><b>DROP TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
            echo "<br>". _('install_puis') ." ...<br>\n";
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
            echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
            echo "</form>\n";
        }
        else
        {
            //on execute le script [nouvelle vesion].sql qui crée et initialise les tables 
            $file_sql="sql/php_conges_v$config_php_conges_version.sql";
            if(file_exists($file_sql))
                $result = execute_sql_file($file_sql,  $DEBUG);


            /*************************************/
            // FIN : mise à jour de la "installed_version" et de la langue dans la table conges_config
            $sql_update_version="UPDATE conges_config SET conf_valeur = '$config_php_conges_version' WHERE conf_nom='installed_version' ";
            $result_update_version = \includes\SQL::query($sql_update_version) ;

            $sql_update_lang="UPDATE conges_config SET conf_valeur = '$lang' WHERE conf_nom='lang' ";
            $result_update_lang = \includes\SQL::query($sql_update_lang) ;

            $tab_url=explode("/", $_SERVER['PHP_SELF']);

            array_pop($tab_url);
            array_pop($tab_url);

            $url_accueil= implode("/", $tab_url) ;  // on prend l'url complet sans le /install/install.php à la fin

            $sql_update_lang="UPDATE conges_config SET conf_valeur = '$url_accueil' WHERE conf_nom='URL_ACCUEIL_CONGES' ";
            $result_update_lang = \includes\SQL::query($sql_update_lang) ;


            $comment_log = "Install de php_conges (version = $config_php_conges_version) ";
            log_action(0, "", "", $comment_log,  $DEBUG);

            /*************************************/
            // on propose la page de config ....
            echo "<br><br><h2>". _('install_ok') ." !</h2><br>\n";

            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=../config/\">";
        }
    }


    // lance les differente maj depuis la $installed_version jusqu'à la version actuelle
    // la $installed_version est préalablement déterminée par get_installed_version() ou renseignée par l'utilisateur
    public static function lance_maj($lang, $installed_version, $config_php_conges_version, $etape, $DEBUG=FALSE)
    {
        if( $DEBUG ) { echo " lang = $lang  ##  etape = $etape ## version = $installed_version<br>\n";}

        $PHP_SELF=$_SERVER['PHP_SELF'];
        include CONFIG_PATH .'dbconnect.php' ;


        //*** ETAPE 0
        if($etape==0)
        {
            //avant tout , on conseille une sauvegarde de la database !! (cf vieux index.php)
            echo "<h3>". _('install_maj_passer_de') ." <font color=\"black\">$installed_version</font> ". _('install_maj_a_version') ." <font color=\"black\">$config_php_conges_version</font> .</h3>\n";
            echo "<h3><font color=\"red\">". _('install_maj_sauvegardez') ." !!!</font></h3>\n";
            echo "<h2>....</h2>\n";
            echo "<br>\n";
            echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
            echo "<input type=\"hidden\" name=\"etape\" value=\"1\">\n";
            echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
            echo "<input type=\"submit\" value=\"". _('form_continuer') ."\">\n";
            echo "</form>\n";
            echo "<br><br>\n";

        }
        //*** ETAPE 1
        elseif($etape==1)
        {
            //verif si create / alter table possible !!!
            if(!\install\Fonctions::test_create_table($DEBUG))
            {
                echo "<font color=\"red\"><b>CREATE TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
                echo "<br>puis ...<br>\n";
                echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
                echo "<input type=\"hidden\" name=\"etape\"value=\"1\" >\n";
                echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
                echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
                echo "</form>\n";
            }
            elseif(!\install\Fonctions::test_alter_table($DEBUG))
            {
                echo "<font color=\"red\"><b>ALTER TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
                echo "<br>puis ...<br>\n";
                echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
                echo "<input type=\"hidden\" name=\"etape\"value=\"1\" >\n";
                echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
                echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
                echo "</form>\n";
            }
            elseif(!\install\Fonctions::test_drop_table($DEBUG))
            {
                echo "<font color=\"red\"><b>DROP TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
                echo "<br>puis ...<br>\n";
                echo "<form action=\"$PHP_SELF?lang=$lang\">\n";
                echo "<input type=\"hidden\" name=\"etape\"value=\"1\" >\n";
                echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
                echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
                echo "</form>\n";
            }
            else
            {
                if( !$DEBUG )
                    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=2&version=$installed_version&lang=$lang\">";
                else
                    echo "<a href=\"$PHP_SELF?etape=2&version=$installed_version&lang=$lang\">". _('install_etape') ." 1  OK</a><br>\n";
            }
        }
        //*** ETAPE 2
        elseif($etape==2)
        {
            // si on part d'une version <= v1.0 : on travaille sinon, on passe à l'étape 3
            if( (substr($installed_version, 0, 1)=="0") || ($installed_version=="1.0") )
            {
                //verif si la copie de l'ancien fichier de config est présent et lisible (install/config_old.php)
                if( !test_old_config_file($DEBUG) )
                {
                    echo "<font color=\"red\">\n";
                    echo  _('install_le_fichier') ." \"<b>install / config_old.php</b>\" ". _('install_inaccessible') ." !<br>\n";
                    echo  _('install_maj_conserv_config') ."<br>\n";
                    echo  _('install_maj_copy_config_file') ." \"<b>install</b>\" ". _('install_maj_whith_name') ." \"<b>config_old.php</b>\" ". _('install_maj_and') ."<br>\n";
                    echo  _('install_maj_verif_droit_fichier') ." <br>\n";
                    echo "</font><br> \n";
                    echo "<br>". _('install_puis') ." ...<br>\n";
                    echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
                    echo "<input type=\"hidden\" name=\"etape\"value=\"2\" >\n";
                    echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
                    echo "<input type=\"submit\" value=\"". _('form_continuer') ."\">\n";
                    echo "</form>\n";
                }
                else
                {
                    if( !$DEBUG )
                        echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=3&version=$installed_version&lang=$lang\">";
                    else
                        echo "<a href=\"$PHP_SELF?etape=3&version=$installed_version&lang=$lang\">". _('install_etape') ." 2  OK</a><br>\n";
                }
            }
            else
            {
                if( !$DEBUG )
                    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=3&version=$installed_version&lang=$lang\">";
                else
                    echo "<a href=\"$PHP_SELF?etape=3&version=$installed_version&lang=$lang\">". _('install_etape') ." 2  OK</a><br>\n";
            }

        }
        //*** ETAPE 3
        elseif($etape==3)
        {
            // ATTENTION on ne passe cette étape que si on est en version inferieure à 1.0 ! (donc en v0.xxx) (sinon on passe à l'étape 4)
            if(substr($installed_version, 0, 1)!="0")
            {
                if( !$DEBUG )
                    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=4&version=$installed_version&lang=$lang\">";
                else
                    echo "<a href=\"$PHP_SELF?etape=4&version=$installed_version&lang=$lang\">". _('install_etape') ." 3  OK</a><br>\n";
            }
            else
            {
                //on lance l'execution de fichier sql de migration l'un après l'autre jusqu a la version 0.10.1 ..
                $db_version=explode(".", $installed_version);
                $db_sub_version = (int) $db_version[1];

                for($i=$db_sub_version ; $i <= 10 ; $i++)
                {
                    if($i==10) // si on en est à v0.10 on passe en v1.0
                        $sql_file = "sql/upgrade_v0.10_to_v1.0.sql";
                    else
                    {
                        $j=$i+1;
                        $sql_file = "sql/upgrade_v0.".$i."_to_v0.".$j.".sql";
                    }
                    if( $DEBUG )
                        echo "sql_file = $sql_file<br>\n";
                    execute_sql_file($sql_file,  $DEBUG);
                }
                if( !$DEBUG )
                    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=4&version=1.0&lang=$lang\">";
                else
                    echo "<a href=\"$PHP_SELF?etape=4&version=1.0&lang=$lang\">". _('install_etape') ." 3  OK</a><br>\n";
            }

        }
        //*** ETAPE 4
        elseif($etape==4)
        {
            // on est au moins à la version 1.0 ....
            // ensuite tout se fait en php (plus de script de migration sql)

            // on determine la version la + élevée entre $installed_version et 1.0  , et on part de celle là !
            if(substr($installed_version, 0, 1)=="0")
                $start_version="1.4.0";
            else
                $start_version=$installed_version ;

            //on lance l'execution (include) des scripts d'upgrade l'un après l'autre jusqu a la version voulue ($config_php_conges_version) ..
            if($start_version=="1.4.0")
            {
                $file_upgrade='upgrade_from_v1.4.0.php';
                $new_installed_version="1.4.1";
                // execute le script php d'upgrade de la version1.4.0 (vers la suivante (1.4.1))
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$file_upgrade?version=$new_installed_version&lang=$lang\">";
            }
            elseif(($start_version=="1.4.1")||($start_version=="1.4.2"))
            {
                $file_upgrade='upgrade_from_v1.4.2.php';
                $new_installed_version="1.5.0";
                // execute le script php d'upgrade de la version1.4.2 (vers la suivante (1.5.0))
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$file_upgrade?version=$new_installed_version&lang=$lang\">";
            }
            elseif(($start_version=="1.5.0")||($start_version=="1.5.1"))
            {
                $file_upgrade='upgrade_from_v1.5.0.php';
                $new_installed_version="1.6.0";
                // execute le script php d'upgrade de la version1.5.0 (vers la suivante (1.6.0))
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$file_upgrade?version=$new_installed_version&lang=$lang\">";
            }
            elseif($start_version=="1.6.0")
            {
                $file_upgrade='upgrade_from_v1.6.0.php';
                $new_installed_version="1.7.0";
                // execute le script php d'upgrade de la version1.6.0 (vers la suivante (1.7.0))
                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$file_upgrade?version=$new_installed_version&lang=$lang\">";
            }
            else
            {
                if( !$DEBUG )
                    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=5&version=1.4.1&lang=$lang\">";
                else
                    echo "<a href=\"$PHP_SELF?etape=5&version=1.5.0&lang=$lang\">". _('install_etape') ." 4  OK</a><br>\n";
            }

        }
        //*** ETAPE 5
        elseif($etape==5)
        {
            // FIN
            // test si fichiers config.php ou config_old.php existent encore (si oui : demande de les éffacer !
            if( (\install\Fonctions::test_config_file($DEBUG)) || (\install\Fonctions::test_old_config_file($DEBUG)) )
            {
                if(test_config_file($DEBUG))
                {
                    echo  _('install_le_fichier') ." <b>\"config.php\"</b> ". _('install_remove_fichier') .".<br> \n";
                }
                if(test_old_config_file($DEBUG))
                {
                    echo  _('install_le_fichier') ." <b>\"install/config_old.php\"</b> ". _('install_remove_fichier') .".<br> \n";
                }
                echo "<br><a href=\"$PHP_SELF?etape=5&version=$config_php_conges_version&lang=$lang\">". _('install_reload_page') ." ....</a><br>\n";
            }
            else
            {
                // mise à jour de la "installed_version" et de la langue dans la table conges_config
                $sql_update_version="UPDATE conges_config SET conf_valeur = '$config_php_conges_version' WHERE conf_nom='installed_version' ";
                $result_update_version = \includes\SQL::query($sql_update_version) ;

                $sql_update_lang="UPDATE conges_config SET conf_valeur = '$lang' WHERE conf_nom='lang' ";
                $result_update_lang = \includes\SQL::query($sql_update_lang) ;

                $comment_log = _('install_maj_titre_2')." (version $installed_version --> version $config_php_conges_version) ";
                log_action(0, "", "", $comment_log,  $DEBUG);

                // on propose la page de config ....
                echo "<br><br><h2>". _('install_ok') ." !</h2><br>\n";

                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=../config/\">";
            }
        }
        else
        {
            // rien, on ne devrait jammais arriver dans ce else !!!
        }
    }

    public static function e1_create_table_plugins()
    {
        $create_table_plugin_query = "CREATE TABLE IF NOT EXISTS `conges_plugins` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `p_name` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Plugin name',
            `p_is_active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Plugin activated ?',
            `p_is_install` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Plugin is installed ?',
            PRIMARY KEY (`id`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";
        $result_create_table_plugin = \includes\SQL::query($create_table_plugin_query);
    }

    public static function e1_insert_into_conges_config( $DEBUG=FALSE)
    {

        $sql_insert_1="INSERT INTO `conges_config` VALUES ('fermeture_bgcolor', '#7B9DE6', '14_Presentation', 'hidden', 'config_comment_fermeture_bgcolor')";
        $result_insert_1 = \includes\SQL::query($sql_insert_1)  ;

        $sql_insert_2="INSERT INTO `conges_config` VALUES ('texte_page_login', '', '02_PAGE D\'AUTENTIFICATION', 'texte', 'config_comment_texte_page_login')";
        $result_insert_2 = \includes\SQL::query($sql_insert_2)  ;

        $sql_insert_3="INSERT INTO `conges_config` VALUES ('solde_toujours_positif', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_solde_toujours_positif')";
        $result_insert_3 = \includes\SQL::query($sql_insert_3)  ;

    }

    public static function e2_drop_table_historique_ajout( $DEBUG=FALSE)
    {

        if(\install\Fonctions::test_create_table($DEBUG))
        {
            if(\install\Fonctions::test_drop_table($DEBUG))
            {
                $sql_drop_1="DROP TABLE `conges_historique_ajout`";
                $result_drop_1 = \includes\SQL::query($sql_drop_1)  ;
            }
        }
    }

    public static function e1_create_table_conges_appli( $DEBUG=FALSE)
    {
        $sql_create="CREATE TABLE IF NOT EXISTS `conges_appli` (
            `appli_variable` varchar(100) binary NOT NULL default '',
            `appli_valeur` varchar(200) binary NOT NULL default '',
            PRIMARY KEY  (`appli_variable`)
                ) DEFAULT CHARSET=latin1; ";
        $result_create = \includes\SQL::query($sql_create);
    }
    
    public static function e2_insert_into_conges_appli( $DEBUG=FALSE)
        {
            $sql_insert_1="INSERT INTO `conges_appli` VALUES ('num_exercice', '1')";
            $result_insert_1 = \includes\SQL::query($sql_insert_1)  ;

            $sql_insert_2="INSERT INTO `conges_appli` VALUES ('date_limite_reliquats', '0')";
            $result_insert_2 = \includes\SQL::query($sql_insert_2)  ;

            $sql_insert_3="INSERT INTO `conges_appli` VALUES ('semaine_bgcolor', '#FFFFFF')";
            $result_insert_3 = \includes\SQL::query($sql_insert_3)  ;

            $sql_insert_4="INSERT INTO `conges_appli` VALUES ('week_end_bgcolor', '#BFBFBF')";
            $result_insert_4 = \includes\SQL::query($sql_insert_4)  ;

            $sql_insert_5="INSERT INTO `conges_appli` VALUES ('temps_partiel_bgcolor', '#FFFFC4')";
            $result_insert_5 = \includes\SQL::query($sql_insert_5)  ;

            $sql_insert_6="INSERT INTO `conges_appli` VALUES ('conges_bgcolor', '#DEDEDE')";
            $result_insert_6 = \includes\SQL::query($sql_insert_6)  ;

            $sql_insert_7="INSERT INTO `conges_appli` VALUES ('demande_conges_bgcolor', '#E7C4C4')";
            $result_insert_7 = \includes\SQL::query($sql_insert_7)  ;

            $sql_insert_8="INSERT INTO `conges_appli` VALUES ('absence_autre_bgcolor', '#D3FFB6')";
            $result_insert_8 = \includes\SQL::query($sql_insert_8)  ;

            $sql_insert_9="INSERT INTO `conges_appli` VALUES ('fermeture_bgcolor', '#7B9DE6')";
            $result_insert_9 = \includes\SQL::query($sql_insert_9)  ;
        }

    public static function e3_delete_from_table_conges_config( $DEBUG=FALSE)
    {
        $sql_delete_1="DELETE FROM conges_config WHERE conf_type = 'hidden' ";
        $result_delete_1 = \includes\SQL::query($sql_delete_1)  ;

        $sql_delete_2="DELETE FROM conges_config WHERE conf_nom = 'rtt_comme_conges' ";
        $result_delete_2 = \includes\SQL::query($sql_delete_2)  ;
    }

    public static function e4_alter_table_conges_users( $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        $sql_alter_1=" ALTER TABLE `conges_users` ADD `u_num_exercice` INT(2) NOT NULL DEFAULT '0' ";
        $result_alter_1 = \includes\SQL::query($sql_alter_1)  ;
    }

    public static function e5_insert_into_conges_config( $DEBUG=FALSE)
    {
        $sql_insert_1="INSERT INTO `conges_config` VALUES ('autorise_reliquats_exercice', 'TRUE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_autorise_reliquats_exercice')";
        $result_insert_1 = \includes\SQL::query($sql_insert_1)  ;

        $sql_insert_2="INSERT INTO `conges_config` VALUES ('nb_maxi_jours_reliquats', '0', '12_Fonctionnement de l\'Etablissement', 'texte', 'config_comment_nb_maxi_jours_reliquats')";
        $result_insert_2 = \includes\SQL::query($sql_insert_2)  ;

        $sql_insert_3="INSERT INTO `conges_config` VALUES ('jour_mois_limite_reliquats', '0', '12_Fonctionnement de l\'Etablissement', 'texte', 'config_comment_jour_mois_limite_reliquats')";
        $result_insert_3 = \includes\SQL::query($sql_insert_3)  ;
    }

    public static function e6_alter_table_conges_solde_user( $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        $sql_alter_1=" ALTER TABLE `conges_solde_user` ADD `su_reliquat` DECIMAL( 4, 2 ) NOT NULL DEFAULT '0' ";
        $result_alter_1 = \includes\SQL::query($sql_alter_1)  ;
    }

    public static function e7_alter_tables_taille_login( $DEBUG=FALSE)
    {
        $PHP_SELF=$_SERVER['PHP_SELF'];

        $sql_alter_1=" ALTER TABLE `conges_artt` CHANGE `a_login` `a_login` VARBINARY( 99 ) NOT NULL ";
        $result_alter_1 = \includes\SQL::query($sql_alter_1)  ;

        $sql_alter_2=" ALTER TABLE `conges_echange_rtt` CHANGE `e_login` `e_login` VARBINARY( 99 ) NOT NULL ";
        $result_alter_2 = \includes\SQL::query($sql_alter_2)  ; 

        $sql_alter_3=" ALTER TABLE `conges_edition_papier` CHANGE `ep_login` `ep_login` VARBINARY( 99 ) NOT NULL ";
        $result_alter_3 = \includes\SQL::query($sql_alter_3)  ; 

        $sql_alter_4=" ALTER TABLE `conges_groupe_grd_resp` CHANGE `ggr_login` `ggr_login` VARBINARY( 99 ) NOT NULL ";
        $result_alter_4 = \includes\SQL::query($sql_alter_4)  ;  

        $sql_alter_5=" ALTER TABLE `conges_groupe_resp` CHANGE `gr_login` `gr_login` VARBINARY( 99 ) NOT NULL ";
        $result_alter_5 = \includes\SQL::query($sql_alter_5)  ;

        $sql_alter_6=" ALTER TABLE `conges_groupe_users` CHANGE `gu_login` `gu_login` VARBINARY( 99 ) NOT NULL ";
        $result_alter_6 = \includes\SQL::query($sql_alter_6)  ;

        $sql_alter_7=" ALTER TABLE `conges_logs` CHANGE `log_user_login_par` `log_user_login_par` VARBINARY( 99 ) NOT NULL , CHANGE `log_user_login_pour` `log_user_login_pour` VARBINARY( 99 ) NOT NULL ";
        $result_alter_7 = \includes\SQL::query($sql_alter_7)  ; 

        $sql_alter_8=" ALTER TABLE `conges_periode` CHANGE `p_login` `p_login` VARBINARY( 99 ) NOT NULL ";
        $result_alter_8 = \includes\SQL::query($sql_alter_8)  ;  

        $sql_alter_9=" ALTER TABLE `conges_solde_user` CHANGE `su_login` `su_login` VARBINARY( 99 ) NOT NULL ";
        $result_alter_9 = \includes\SQL::query($sql_alter_9)  ;

        $sql_alter_10=" ALTER TABLE `conges_users` CHANGE `u_login` `u_login` VARBINARY( 99 ) NOT NULL , CHANGE `u_resp_login` `u_resp_login` VARBINARY( 99 ) NULL DEFAULT NULL ";
        $result_alter_10 = \includes\SQL::query($sql_alter_10)  ;
    }
}
