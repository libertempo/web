<?php
define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

//include_once ROOT_PATH .'fonctions_conges.php' ;
$_SESSION['lang'] = 'fr_FR';

include_once INCLUDE_PATH .'fonction.php';
session_delete();
include_once ROOT_PATH .'fonctions_conges.php' ;
if (!empty(session_id())) {
    session_regenerate_id(false);
}

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

// TODO 1.10 : la suppression des langues se fait en plusieurs temps.
// Les versions suivantes devront supprimer toute info liée aux langues.
$lang = 'fr_FR';
//recup de la config db
$dbserver=(isset($_GET['dbserver']) ? $_GET['dbserver'] : ((isset($_POST['dbserver'])) ? $_POST['dbserver'] : "") ) ;
$dbserver = htmlentities($dbserver, ENT_QUOTES | ENT_HTML401);

$dbuser=(isset($_GET['dbuser']) ? $_GET['dbuser'] : ((isset($_POST['dbuser'])) ? $_POST['dbuser'] : "") ) ;
$dbuser = htmlentities($dbuser, ENT_QUOTES | ENT_HTML401);

$dbpasswd=(isset($_GET['dbpasswd']) ? $_GET['dbpasswd'] : ((isset($_POST['dbpasswd'])) ? $_POST['dbpasswd'] : "") ) ;
$dbpasswd = htmlentities($dbpasswd, ENT_QUOTES | ENT_HTML401);

$dbdb=(isset($_GET['dbdb']) ? $_GET['dbdb'] : ((isset($_POST['dbdb'])) ? $_POST['dbdb'] : "") ) ;
$dbdb = htmlentities($dbdb, ENT_QUOTES | ENT_HTML401);

    if(\install\Fonctions::test_dbconnect_file()!=TRUE) {
        $_SESSION['lang']=$lang;

        header_popup();
        echo "<center>\n";
        echo "<br><br>\n";
        if($dbserver=="" || $dbuser=="" || $dbpasswd=="") {
            echo  _('db_configuration');
            echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
            echo  _('db_configuration_server');
            echo '<INPUT type="text" value="localhost" name="dbserver"><br>';
            echo  _('db_configuration_name');
            echo '<INPUT type="text" value="db_conges" name="dbdb"><br>';
            echo  _('db_configuration_user');
            echo '<INPUT type="text" value="conges" name="dbuser"><br>';
            echo  _('db_configuration_password');
            echo '<INPUT type="password" name="dbpasswd" autocomplete="off"><br>';
            echo "<INPUT type=\"hidden\" value=\"".$lang."\" name=\"lang\"><br>";
            echo "<br>\n";
            echo "<input type=\"submit\" value=\"OK\">\n";
            echo "</form>\n";

        } else {
            $is_dbconf_ok= \install\Fonctions::write_db_config($dbserver,$dbuser,$dbpasswd,$dbdb);
            if($is_dbconf_ok!=true) {
                echo "le dossier ".CONFIG_PATH." n'est pas accessible en écriture";
            } else {
                echo _('db_configuration_ok');
                echo "<br><a href=\"$PHP_SELF?lang=$lang\"> continuez....</a><br>\n";
            }
        }
        bottom();
    } else {
        include_once CONFIG_PATH .'dbconnect.php';
        include_once ROOT_PATH .'version.php';

        $data = ['serveur' => $mysql_serveur, 'base' => $mysql_database, 'user' => $mysql_user, 'password' => $mysql_pass];
        try {
            \install\Fonctions::setDataConfigurationApi($data);
        } catch (\Exception $e) {
            echo 'Échec de l\'installation / mise à jour : ' . $e->getMessage();
            exit();
        }


        if(!\install\Fonctions::test_database()) {
            header_popup();
            echo "<center>\n";
            echo "<br><br>\n";
            echo "<b>". _('install_db_inaccessible') ." ... <br><br>\n";
            echo  _('install_verifiez_param_file');
            echo "(". _('install_verifiez_priv_mysql') .")<br><br>\n";

            echo "<center>\n";
            echo "<br><br>\n";
            if($dbserver=="" || $dbuser=="" || $dbpasswd=="") {
                echo  _('db_configuration');
                echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
                echo  _('db_configuration_server');
                echo '<INPUT type="text" value="localhost" name="dbserver"><br>';
                echo  _('db_configuration_name');
                echo '<INPUT type="text" value="db_conges" name="dbdb"><br>';
                echo  _('db_configuration_user');
                echo '<INPUT type="text" value="conges" name="dbuser"><br>';
                echo  _('db_configuration_password');
                echo '<INPUT type="password" name="dbpasswd" autocomplete="off"><br>';
                echo "<INPUT type=\"hidden\" value=\"".$lang."\" name=\"lang\"><br>";
                echo "<br>\n";
                echo "<input type=\"submit\" value=\"OK\">\n";
                echo "</form>\n";
            } else {
                $is_dbconf_ok=write_db_config($dbserver,$dbuser,$dbpasswd,$dbdb);
                if($is_dbconf_ok!=true) {
                    echo "le dossier ".CONFIG_PATH." n'est pas accessible en écriture";
                } else {
                    echo _('db_configuration_ok');
                    echo "<br><a href=\"$PHP_SELF?lang=$lang\"> continuez....</a><br>\n";
                }
            }

            bottom();
        } else {
            $installed_version = \install\Fonctions::get_installed_version();

            if($installed_version==0)   // num de version inconnu
            {
                \install\Fonctions::install($lang);
            } else {
                // on compare la version déclarée dans la database avec la version déclarée dans le fichier de config
                if($installed_version != $config_php_conges_version) {
                    // on attaque une mise a jour à partir de la version installée
                    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=mise_a_jour.php?version=$installed_version&lang=$lang\">";
                } else {
                    // pas de mise a jour a faire : on propose les pages de config
                    echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=../config/\">";
                }
            }
        }
    }
