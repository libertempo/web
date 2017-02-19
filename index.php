<?php
define('ROOT_PATH', '');
require_once 'define.php';

// test si dbconnect.php est présent !
if (!is_readable( CONFIG_PATH .'dbconnect.php'))
{
	header("Location:". ROOT_PATH .'install/');
}

include_once INCLUDE_PATH .'fonction.php';
include_once ROOT_PATH .'fonctions_conges.php'; // for init_config_tab()

$config = new \App\Libraries\Configuration();

$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config


/***** DEBUT DU PROG *****/

/*** initialisation des variables ***/
/************************************/

if($err = getpost_variable('error', false))
{
	switch ($err) {
		case 'session-invalid':
			header_error();
			echo "<p>" . _('session_pas_session_ouverte') . "<p>\n";
			echo "<p>" . _('divers_veuillez') ." <a href='" .$_SESSION['config']['URL_ACCUEIL_CONGES'] . "/index.php' target='_top'><strong>" . _('divers_vous_authentifier') . "</strong></a></p>\n";
			bottom();
			exit();
			break;
	}
}

if($_SESSION['config']['auth'] == FALSE)    // si pas d'autentification (cf config de php_conges)
{
	$login = getpost_variable('login');
	if(empty($login))
	{
	    // redirect( ROOT_PATH .'erreur.php?error_num=1');

		header_error();
		printf("<h1>ERREUR !</h1>\n");
		// authentification Error
		echo '<p>' . _('erreur_user') . "</p>\n";
		echo '<p>' . _('erreur_login_password') . "</p>\n" ;
		bottom();

		exit();
	}
	else
	{
		if(session_id()!="")
			session_destroy();

		// on initialise la nouvelle session
		ini_set ( "session.gc_maxlifetime", $_SESSION['config']['duree_session'] );
		session_create($login);
	}
}
else
{
	$session_username = isset($_POST['session_username']) ? $_POST['session_username'] : '';
	$session_password = isset($_POST['session_password']) ? $_POST['session_password'] : '';

	if(session_id()!="")
		session_destroy();

	// Si CAS alors on utilise le login CAS pour la session
	if ( $config->getHowToConnectUser() == "cas" && $_GET['cas'] != "no" )
	{
		//redirection vers l'url d'authentification CAS
		$usernameCAS = authentification_passwd_conges_CAS();
		if($usernameCAS == "") {
			header_error();

			echo  _('session_pas_de_compte_dans_dbconges') ."<br>\n";
			echo  _('session_contactez_admin') ."\n";

			$URL_ACCUEIL_CONGES=$_SESSION['config']['URL_ACCUEIL_CONGES'];
			deconnexion_CAS($URL_ACCUEIL_CONGES);
			bottom();
			exit;
		}
	}
	// Si SSO, on utilise les identifiants de session pour se connecter
	else if ( $config->getHowToConnectUser() == "SSO" )
	{
		$usernameSSO = authentification_AD_SSO();
		if($usernameSSO != "")
		{
			session_create( $usernameSSO );
		}
		else//dans ce cas l'utilisateur n'a pas encore été enregistré dans la base de données db_conges
		{
			header_error();

			echo  _('session_pas_de_compte_dans_dbconges') ."<br>\n";
			echo  _('session_contactez_admin') ."\n";

			bottom();
			exit;
		}
	}
	else
	{
		if (($session_username == "") || ($session_password == "")) // si login et passwd non saisis
		{
			//  SAISIE LOGIN / PASSWORD :
			session_saisie_user_password("", "", ""); // appel du formulaire d'authentification (login/password)

			exit;
		}
		else
		{
			//  AUTHENTIFICATION :
			// le user doit etre authentifié dans la table conges (login + passwd) ou dans le ldap.
			// si on a trouve personne qui correspond au couple user/password

			if ( $config->getHowToConnectUser() == "ldap" && $session_username != "admin" )
			{
				$username_ldap = authentification_ldap_conges($session_username,$session_password);
				if ( $username_ldap != $session_username)
				{
					$session="";
					$session_username="";
					$session_password="";
					$erreur="login_passwd_incorrect";
					// appel du formulaire d'intentification (login/password)
					session_saisie_user_password($erreur, $session_username, $session_password);

					exit;
				}
				else
				{
					if (valid_ldap_user($session_username)) // LDAP ok, on vérifie ici que le compte existe dans la base de données des congés.
					{
						// on initialise la nouvelle session
						session_create($session_username);
					}
					else//dans ce cas l'utilisateur n'a pas encore été enregistré dans la base de données db_conges
					{
						header_error();

						echo  _('session_pas_de_compte_dans_dbconges') ."<br>\n";
						echo  _('session_contactez_admin') ."\n";

						bottom();
						exit;
					}
				}
			} // fin du if test avec ldap
			elseif ( $config->getHowToConnectUser() == "dbconges" || $session_username == "admin" )
			{
				$username_conges = autentification_passwd_conges($session_username,$session_password);
				if ( $username_conges != $session_username)
				{
					$session="";
					$session_username="";
					$session_password="";
					$erreur="login_passwd_incorrect";
					// appel du formulaire d'intentification (login/password)
					session_saisie_user_password($erreur, $session_username, $session_password);

					exit;
				}
				else
				{
					// on initialise la nouvelle session
					session_create($session_username);
				}
			}
		}
	}
}

/*****************************************************************/

if(isset($_SESSION['userlogin']))
{
	$request= "SELECT u_nom, u_passwd, u_prenom, u_is_resp, u_is_hr, u_is_admin  FROM conges_users where u_login = '". \includes\SQL::quote($_SESSION['userlogin'])."' " ;
	$rs = \includes\SQL::query($request );
	if($rs->num_rows != 1)
	{
	    redirect( ROOT_PATH .'index.php' );
	}
	else
	{
		$session=session_id();
		$row = $rs->fetch_array();
		$NOM=$row["u_nom"];
		$PRENOM=$row["u_prenom"];
        $is_admin = $row["u_is_admin"];
        $is_hr = $row["u_is_hr"];
		$is_resp = $row["u_is_resp"];

		// si le login est celui d'un responsable ET on est pas en mode "responsable virtuel"
		// OU on est en mode "responsable virtuel" avec login= celui du resp virtuel
		$return_url = getpost_variable('return_url');
		if (!empty($return_url))
		{
			if (strpos($return_url,'?'))
				redirect( ROOT_PATH . $return_url .'&session=' . $session );
			else
				redirect( ROOT_PATH .$return_url . '?session=' . $session );
		}
		elseif ('Y' === $is_admin)
		{
			redirect( ROOT_PATH .'admin/admin_index.php?session=' . $session );
		}
        elseif ( $is_hr == "Y" )
		{
			redirect( ROOT_PATH .'hr/hr_index.php?session=' . $session );
		}
		elseif ( $is_resp=="Y" )
		{
			// redirection vers responsable/resp_index.php
			redirect( ROOT_PATH .'responsable/resp_index.php?session=' . $session );
		}
		else
		{
			// redirection vers utilisateur/user_index.php
			redirect( ROOT_PATH . 'utilisateur/user_index.php?session=' . $session );
		}

	}
}
