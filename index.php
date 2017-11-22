<?php
define('ROOT_PATH', '');
require_once 'define.php';

// test si dbconnect.php est présent !
if (!is_readable( CONFIG_PATH .'dbconnect.php')) {
	header("Location:". ROOT_PATH .'install/');
}
include_once INCLUDE_PATH .'fonction.php';
include_once ROOT_PATH .'fonctions_conges.php'; // for init_config_tab()
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
$injectableCreator = new \App\Libraries\InjectableCreator(\includes\SQL::singleton());
$api = $injectableCreator->get(\App\Libraries\ApiClient::class);

/***** DEBUT DU PROG *****/

if (!session_is_valid()) {

	/*** initialisation des variables ***/
	/************************************/
	if ($err = getpost_variable('error', false)) {
		switch ($err) {
			case 'session-invalid':
				header_error();
				echo "<p>" . _('session_pas_session_ouverte') . "<p>\n";
				echo "<p>" . _('divers_veuillez') ." <a href='" . $config->getUrlAccueil() . "/index.php' target='_top'><strong>" . _('divers_vous_authentifier') . "</strong></a></p>\n";
				bottom();
				exit();
		}
	}

	// Si CAS alors on utilise le login CAS pour la session
	if ( $config->getHowToConnectUser() == "cas" && $_GET['cas'] != "no" ) {
	        //redirection vers l'url d'authentification CAS
	        $usernameCAS = authentification_passwd_conges_CAS();
	        if ($usernameCAS == "") {
	                header_error();

	                echo  _('session_pas_de_compte_dans_dbconges') ."<br>\n";
	                echo  _('session_contactez_admin') ."\n";

	                $URL_ACCUEIL_CONGES = $config->getUrlAccueil();
	                deconnexion_CAS($URL_ACCUEIL_CONGES);
	                bottom();
	                exit;
	        }
	} elseif ( $config->getHowToConnectUser() == "SSO" ) {
	// Si SSO, on utilise les identifiants de session pour se connecter
	    if (session_id()!="")
	        session_destroy();

	        $usernameSSO = authentification_AD_SSO();
	        if ($usernameSSO != "") {
	                session_create( $usernameSSO );
	                storeTokenApi($api, $usernameSSO, '');
	        } else { //dans ce cas l'utilisateur n'a pas encore été enregistré dans la base de données db_conges
	                header_error();

	                echo  _('session_pas_de_compte_dans_dbconges') ."<br>\n";
	                echo  _('session_contactez_admin') ."\n";

	                bottom();
	                exit;
	        }
	} else {
	    $session_username = isset($_POST['session_username']) ? $_POST['session_username'] : '';
	    $session_password = isset($_POST['session_password']) ? $_POST['session_password'] : '';

	    if (session_id()!="")
	        session_destroy();

        if (($session_username == "") || ($session_password == "")) { // si login et passwd non saisis
                //  SAISIE LOGIN / PASSWORD :
                session_saisie_user_password("", "", ""); // appel du formulaire d'authentification (login/password)

                exit;
        } else {
            //  AUTHENTIFICATION :
            // le user doit etre authentifié dans la table conges (login + passwd) ou dans le ldap.
            // si on a trouve personne qui correspond au couple user/password

            if ($config->getHowToConnectUser() == "ldap" && $session_username != "admin") {
                $username_ldap = authentification_ldap_conges($session_username,$session_password);
                if ($username_ldap != $session_username)
                {
                        $session_username="";
                        $session_password="";
                        $erreur="login_passwd_incorrect";
                        // appel du formulaire d'intentification (login/password)
                        session_saisie_user_password($erreur, $session_username, $session_password);

                        exit;
                } else {
                        if (valid_ldap_user($session_username)) { // LDAP ok, on vérifie ici que le compte existe dans la base de données des congés.
                                // on initialise la nouvelle session
                                session_create($session_username);
                                storeTokenApi($api, $session_username, $session_password);
                        } else { //dans ce cas l'utilisateur n'a pas encore été enregistré dans la base de données db_conges
                                header_error();

                                echo  _('session_pas_de_compte_dans_dbconges') ."<br>\n";
                                echo  _('session_contactez_admin') ."\n";

                                bottom();
                                exit;
                        }
                }
            } elseif ($config->getHowToConnectUser() == "dbconges" || $session_username == "admin") { // fin du if test avec ldap
                $username_conges = autentification_passwd_conges($session_username,$session_password);
                if ($username_conges != $session_username) {
                        $session_username="";
                        $session_password="";
                        $erreur="login_passwd_incorrect";
                        // appel du formulaire d'intentification (login/password)
                        session_saisie_user_password($erreur, $session_username, $session_password);

                        exit;
                } else {
                        // on initialise la nouvelle session
                        session_create($session_username);
                        storeTokenApi($api, $session_username, $session_password);
                }
            }
    	}
	}
}

/*****************************************************************/

if (isset($_SESSION['userlogin'])) {
	$request= "SELECT u_nom, u_passwd, u_prenom, u_is_resp, u_is_hr, u_is_admin, u_is_active  FROM conges_users where u_login = '". \includes\SQL::quote($_SESSION['userlogin'])."' " ;
	$rs = \includes\SQL::query($request );
	if ($rs->num_rows != 1) {
	    redirect( ROOT_PATH .'index.php' );
	} else {
		$row = $rs->fetch_array();
		$NOM=$row["u_nom"];
		$PRENOM=$row["u_prenom"];
        $is_admin = $row["u_is_admin"];
        $is_hr = $row["u_is_hr"];
		$is_resp = $row["u_is_resp"];
		$is_active = $row["u_is_active"];
		if ($is_active == "N") {
			header_error();
			echo  _('session_compte_inactif') ."<br>\n";
			echo  _('session_contactez_admin') ."\n";
			bottom();
			exit;
		}
		// si le login est celui d'un responsable ET on est pas en mode "responsable virtuel"
		// OU on est en mode "responsable virtuel" avec login= celui du resp virtuel
		$return_url = getpost_variable('return_url');
		if (!empty($return_url)) {
			if (strpos($return_url,'?'))
				redirect( ROOT_PATH . $return_url);
			else
				redirect( ROOT_PATH .$return_url);
		}
                elseif ( $is_hr == "Y" )
		{
			redirect( ROOT_PATH .'hr/hr_index.php');
		} elseif ( $is_resp=="Y" ) {
			// redirection vers responsable/resp_index.php
			redirect( ROOT_PATH .'responsable/resp_index.php');
		} else {
			// redirection vers utilisateur/user_index.php
			redirect( ROOT_PATH . 'utilisateur/user_index.php');
		}

	}
}
