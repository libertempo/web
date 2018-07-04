<?php declare(strict_types = 1);
defined('ROOT_PATH') or define('ROOT_PATH', '');
defined('INCLUDE_PATH') or define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';
require CONFIG_PATH . 'dbconnect.php';

// L'installation a-t-elle été faite ?
if (!\includes\SQL::existsDatabase($mysql_database)) {
    echo "L'application n'est pas installée. Veuillez passer par l'installateur.";
    exit();
}
include_once INCLUDE_PATH .'fonction.php';
include_once INCLUDE_PATH .'fonctions_conges.php'; // for init_config_tab()
$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration($sql);
$injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
$api = $injectableCreator->get(\App\Libraries\ApiClient::class);

function errorAuthentification() {
    header_error();
    echo  _('session_pas_de_compte_dans_dbconges') ."<br>\n";
    echo  _('session_contactez_admin') ."\n";
    bottom();
}

if (!session_is_valid()) {
	if ($config->getHowToConnectUser() == "cas") {
        try {
            $usernameCAS = authentification_passwd_conges_CAS();
            if ($usernameCAS == "") {
                throw new \Exception("Nom d'utilisateur vide");
            }
            session_create($usernameCAS);
            storeTokenApi($api, $usernameCAS, '');
        } catch (\Exception $e) {
            errorAuthentification();
            deconnexion_CAS($config->getUrlAccueil());
        }
	} elseif ($config->getHowToConnectUser() == "SSO") {
        if (session_id() != "") {
            session_destroy();
        }
        try {
            $usernameSSO = authentification_AD_SSO();
            if ($usernameSSO == "") {
                throw new \Exception("Nom d'utilisateur vide");
            }
            session_create($usernameSSO);
            storeTokenApi($api, $usernameSSO, '');
        } catch (\Exception $e) {
            errorAuthentification();
        }
	} else {
	    $session_username = isset($_POST['session_username']) ? $_POST['session_username'] : '';
	    $session_password = isset($_POST['session_password']) ? $_POST['session_password'] : '';
        if (session_id() != "") {
            session_destroy();
        }

        if (($session_username == "") || ($session_password == "")) {
            session_saisie_user_password("", "", "");
        } else {
            if ($config->getHowToConnectUser() == "ldap" && $session_username != "admin") {
                $username_ldap = authentification_ldap_conges($session_username,$session_password);
                if ($username_ldap != $session_username) {
                    $session_username="";
                    $session_password="";
                    $erreur="login_passwd_incorrect";
                    session_saisie_user_password($erreur, $session_username, $session_password);
                } else {
                    try {
                        if (!valid_ldap_user($session_username)) {
                            throw new \Exception("Nom d'utilisateur invalide");
                        }
                        session_create($session_username);
                        storeTokenApi($api, $session_username, $session_password);
                    } catch (\Exception $e) {
                        errorAuthentification();
                    }
                }
            } elseif ($config->getHowToConnectUser() == "dbconges" || $session_username == "admin") {
                $username_conges = authentification_passwd_conges($session_username,$session_password);
                try {
                    if ($username_conges != $session_username) {
                        throw new \Exception("Noms d'utilisateurs différents");
                    }
                    session_create($session_username);
                    storeTokenApi($api, $session_username, $session_password);
                } catch (\Exception $e) {
                    $session_username="";
                    $session_password="";
                    $erreur="login_passwd_incorrect";
                    session_saisie_user_password($erreur, $session_username, $session_password);
                }
            }
    	}
	}
}

if (isset($_SESSION['userlogin'])) {
	$request= "SELECT u_nom, u_passwd, u_prenom, u_is_resp, u_is_hr, u_is_admin, u_is_active  FROM conges_users where u_login = '". \includes\SQL::quote($_SESSION['userlogin'])."' " ;
	$rs = \includes\SQL::query($request );
	if ($rs->num_rows != 1) {
        redirect(ROOT_PATH . 'authentification');
	} else {
		$row = $rs->fetch_array();
		$NOM=$row["u_nom"];
		$PRENOM=$row["u_prenom"];
        $is_admin = $row["u_is_admin"];
        $is_hr = $row["u_is_hr"];
		$is_resp = $row["u_is_resp"];
		$is_active = $row["u_is_active"];
                if('admin' === $_SESSION['userlogin']){
                    redirect(ROOT_PATH . 'admin/admin_index.php');
                }
		if($is_active == "N") {
			header_error();
			echo  _('session_compte_inactif') ."<br>\n";
			echo  _('session_contactez_admin') ."\n";
			bottom();
			exit;
		}
        if ($is_hr == "Y") {
            redirect( ROOT_PATH .'hr/hr_index.php');
		} elseif ($is_resp=="Y") {
            redirect( ROOT_PATH .'responsable/resp_index.php');
		} else {
            redirect( ROOT_PATH . 'utilisateur/user_index.php');
		}
	}
}
