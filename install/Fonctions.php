<?php
namespace install;

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
    public static function install($lang)
    {
        // soit, c'est une install complète , soit c'est une mise à jour d'une version non déterminée

        header_popup('PHP_CONGES : Installation');

        // affichage du titre
        echo "<center>\n";
        echo "<br><H1><img src=\"". IMG_PATH ."tux_config_32x32.png\" width=\"32\" height=\"32\" border=\"0\" title=\"". _('install_install_phpconges') ."\" alt=\"". _('install_install_phpconges') ."\"> ". _('install_index_titre') ."</H1>\n";
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
        echo "<td><img src=\"". IMG_PATH ."shim.gif\" width=\"100\" height=\"10\" border=\"0\" vspace=\"0\" hspace=\"0\"></td>\n";
        echo "<td valign=top>\n";
        echo "<h3>... ". _('install_mise_a_jour') ."</h3><b>". _('install_indiquez_pre_version') ." :</b><br><br>\n";

        // Formulaire : lance mise_a_jour.php
        echo "<form action=\"mise_a_jour.php\" method=\"POST\">\n";
        // affichage de la liste des versions ...
        echo "<select name=\"version\">\n";
        echo "<option value=\"0\">". _('install_installed_version') ."</option>\n";
        echo "<option value=\"1.9\">v1.9</option>\n";
        echo "<option value=\"1.8.1\">v1.8.1</option>\n";
        echo "<option value=\"1.8\">v1.8</option>\n";
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

    public static function lance_install($lang)
    {

        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

        include CONFIG_PATH . 'dbconnect.php';
        include ROOT_PATH .'version.php' ;

        //verif si create / alter table possible !!!
        if(!\install\Fonctions::test_create_table())
        {
            echo "<font color=\"red\"><b>CREATE TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
            echo "<br>". _('install_puis') ." ...<br>\n";
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
            echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
            echo "</form>\n";
        }
        elseif(!\install\Fonctions::test_drop_table())
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
            if(file_exists($file_sql)) {
                $result = execute_sql_file($file_sql);
            }
            if (0 <= version_compare($config_php_conges_version, '1.9')) {
                \includes\SQL::query('UPDATE `conges_appli` SET appli_valeur =  "' . hash('sha256', time() . rand()) . '" WHERE appli_variable = "token_instance"');
            }

            /*************************************/
            // FIN : mise à jour de la "installed_version" et de la langue dans la table conges_config
            $sql_update_version="UPDATE conges_config SET conf_valeur = '$config_php_conges_version' WHERE conf_nom='installed_version' ";
            $result_update_version = \includes\SQL::query($sql_update_version) ;

            $sql_update_lang="UPDATE conges_config SET conf_valeur = '$lang' WHERE conf_nom='lang' ";
            $result_update_lang = \includes\SQL::query($sql_update_lang) ;
            /* Prénommage de l'instance et pointage API */
            self::addInstanceName(\includes\SQL::singleton());

            $comment_log = "Install de php_conges (version = $config_php_conges_version) ";
            log_action(0, "", "", $comment_log);

            /*************************************/
            // on propose la page de config ....
            echo "<br><br><h2>". _('install_ok') ." !</h2><br>\n";

            echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=../config/\">";
        }
    }


    // lance les differente maj depuis la $installed_version jusqu'à la version actuelle
    // la $installed_version est préalablement déterminée par get_installed_version() ou renseignée par l'utilisateur
    public static function lance_maj($lang, $installed_version, $config_php_conges_version, $etape)
    {

        $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
        include CONFIG_PATH .'dbconnect.php' ;


        //*** ETAPE 0
        if($etape==0)
        {
            //avant tout , on conseille une sauvegarde de la database !! (cf vieux index.php)
            echo "<h3>". _('install_maj_passer_de') ." <font color=\"black\">$installed_version</font> ". _('install_maj_a_version') ." <font color=\"black\">$config_php_conges_version</font>.</h3>\n";
            echo "<h3><font color=\"red\">". _('install_maj_cas_echec_backup') .".</font></h3>\n";
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
            if(!\install\Fonctions::test_create_table())
            {
                echo "<font color=\"red\"><b>CREATE TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
                echo "<br>puis ...<br>\n";
                echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
                echo "<input type=\"hidden\" name=\"etape\"value=\"1\" >\n";
                echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
                echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
                echo "</form>\n";
            }
            elseif(!\install\Fonctions::test_alter_table())
            {
                echo "<font color=\"red\"><b>ALTER TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
                echo "<br>puis ...<br>\n";
                echo "<form action=\"$PHP_SELF?lang=$lang\" method=\"POST\">\n";
                echo "<input type=\"hidden\" name=\"etape\"value=\"1\" >\n";
                echo "<input type=\"hidden\" name=\"version\" value=\"$installed_version\">\n";
                echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
                echo "</form>\n";
            }
            elseif(!\install\Fonctions::test_drop_table())
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
                    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?etape=2&version=$installed_version&lang=$lang\">";
            }
        } elseif($etape==2) {
            $start_version = $installed_version ;

            if($start_version == "1.8") {
                $file_upgrade = 'upgrade_from_v1.8.php';
                $new_installed_version = "1.8.1";
            } elseif($start_version == "1.8.1") {
                $file_upgrade = 'upgrade_from_v1.8.1.php';
                $new_installed_version = "1.9";
            } elseif($start_version == "1.9") {
                $file_upgrade = 'upgrade_from_v1.9.php';
                $new_installed_version = "1.10";
            } elseif($start_version == "1.10") {
                $file_upgrade = 'upgrade_from_v1.10.php';
                $new_installed_version = "1.11";
            } else {
                $file_upgrade = '';
                $new_installed_version = $installed_version;
                $etape = 3;
            }
            try {
                if ($new_installed_version !== $start_version) {
                    \admin\Fonctions::sauvegardeAsFile($start_version, $new_installed_version);
                }
                echo '<META HTTP-EQUIV=REFRESH CONTENT="0; URL=' . $file_upgrade . '?etape=' . $etape . '&version=' . $new_installed_version . '&lang=' . $lang . '">';
            } catch (\Exception $e) {
                echo 'Abandon de la mise à jour : ' . $e->getMessage();
            }
        } elseif($etape == 3) {
            /* Reset du token d'instance à chaque version */
            \includes\SQL::query('UPDATE `conges_appli` SET appli_valeur =  "' . hash('sha256', time() . rand()) . '" WHERE appli_variable = "token_instance"');
            // FIN
            // test si fichiers config.php ou config_old.php existent encore (si oui : demande de les effacer !
            if( (\install\Fonctions::test_config_file()) || (\install\Fonctions::test_old_config_file()) )
            {
                if(test_config_file())
                {
                    echo  _('install_le_fichier') ." <b>\"config.php\"</b> ". _('install_remove_fichier') .".<br> \n";
                }
                if(test_old_config_file())
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
                log_action(0, "", "", $comment_log);

                // on propose la page de config ....
                echo "<br><br><h2>". _('install_ok') ." !</h2><br>\n";

                echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=../config/\">";
            }
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

    /**
     * Définit les données de configuration pour l'API
     *
     * @param array $data Données de configuration
     *
     * @throws \Exception En cas d'échec d'écriture
     */
    public static function setDataConfigurationApi(array $data)
    {
        $data = [
            'db' => [
                'serveur' => $data['serveur'],
                'base' => $data['base'],
                'utilisateur' => $data['user'],
                'mot_de_passe' => $data['password'],
            ],
        ];
        if (false === file_put_contents(API_SYSPATH . 'configuration.json', json_encode($data))) {
            throw new \Exception('Création du fichier de config API impossible. Les droits sont-ils bien configurés ?');
        }
    }

    /**
     *
     * @param \includes\SQL $db DB
     */
    public static function addInstanceName(\includes\SQL $db)
    {
        $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $positionInstall = stripos($url, 'install/');
        if (false === $positionInstall) {
            throw new \Exception("Le logiciel n'est pas installé correctement. veuillez recommencer");
        }
        $instance = mb_substr($url, 0, $positionInstall);

        $requete = 'UPDATE `conges_config` SET conf_valeur = "' . $db->quote($instance) . '"
        WHERE conf_nom = "URL_ACCUEIL_CONGES" LIMIT 1';
        $db->query($requete);
        $path = parse_url($instance, \PHP_URL_PATH);
        $contentFile = file_get_contents(API_PATH . '.htaccess.example');
        $newContent = str_replace('vendor', $path . 'vendor', $contentFile);
        file_put_contents(API_PATH . '.htaccess', $newContent);

        return $instance;
    }
}
