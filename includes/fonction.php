<?php
include_once INCLUDE_PATH . 'lang_profile.php';

function schars($htmlspec) {
    return htmlspecialchars( $htmlspec );
}

function redirect($url, $auto_exit = true) {
    // $url = urlencode($url);
    if (headers_sent()) {
        echo '<html>';
            echo '<head>';
                echo '<meta HTTP-EQUIV="REFRESH" CONTENT="0; URL='.$url.'">';
                echo '<script language=javascript>
                        function redirection(page){
                            window.location=page;
                        }
                        setTimeout(\'redirection("'.$url.'")\',100);
                    </script>';
            echo '</head>';
        echo '</html>';
    } else {
        header('Location: '.$url);
    }
    if ($auto_exit)
        exit;
}


function header_popup($title = '', $additional_head = '' ) {
    global $type_bottom;

    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    } else
        throw new Exception('Warning : Ne peux ouvrir deux header !!! previous = '.$last_use['file']);

    $type_bottom = 'popup';

    if (empty($title))
        $title = 'Libertempo';

    include ROOT_PATH .'version.php';
    include_once TEMPLATE_PATH . 'popup_header.php';
}

function header_error() {
    global $type_bottom;

    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    } else
        throw new Exception('Warning : Ne peux ouvrir deux header !!! previous = '.$last_use['file']);

    $type_bottom = 'error';

    $title = 'Libertempo';

    include ROOT_PATH .'version.php';
    include_once TEMPLATE_PATH . 'error_header.php';
}

function header_login($title = '', $additional_head = '' ) {
    global $type_bottom;
    include ROOT_PATH .'version.php';

    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    }else
        throw new Exception('Warning : Ne peux ouvrir deux header !!! previous = '.$last_use['file']);

    $type_bottom = 'login';

    if (empty($title))
        $title = 'Libertempo';

    include_once TEMPLATE_PATH . 'login_header.php';
}

function header_menu($info, $title = '', $additional_head = '' ) {
    global $type_bottom;

    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    }else
        throw new Exception('Warning : Ne peux ouvrir deux header !!! previous = '.$last_use['file']);

    $type_bottom = 'menu';

    if (empty($title))
        $title = 'Libertempo';

    include ROOT_PATH .'version.php';
    include_once TEMPLATE_PATH . 'menu_header.php';
}

function bottom() {
    global $type_bottom;


    static $last_use = '';
    if ($last_use == '') {
        $last_use = debug_backtrace();
    }else
        throw new Exception('Warning : Ne peut ouvrir deux header !!!');

    include_once TEMPLATE_PATH . $type_bottom .'_bottom.php';
}


//
// indique (TRUE / FALSE) si une session est valide (par / au temps de connexion)
//
function session_is_valid()
{
    if (session_id() == "") {
        session_start();
    }

    if (isset($_SESSION['token']) && isset($_SESSION['timestamp_last']) && isset($_SESSION['config'])) {
        return time() < $_SESSION['timestamp_last'];
    }

    return false;
}

//
// cree la session et renvoie son identifiant
//
function session_create($username)
{
    if ($username != "") {
        if (isset($_SESSION)) unset($_SESSION);

        session_start();
        session_regenerate_id();
        $_SESSION['userlogin']=$username;
        $maintenant=time();
        $_SESSION['timestamp_start']=$maintenant;
        $_SESSION['timestamp_last']= $maintenant + SESSION_DURATION;
        if (function_exists('init_config_tab'))
            $_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config

        if (isset($_REQUEST['lang']))
            $_SESSION['lang'] = $_REQUEST['lang'];
    }


    $comment_log = 'Connexion de '.$username;
    log_action(0, "", $username, $comment_log);

    return;
}

//
// mise a jour d'une session
//
function session_update($session)
{
    $_SESSION['timestamp_last'] = time() + SESSION_DURATION;
}

//
// destruction d'une session
//
function session_delete()
{
     unset($_SESSION['userlogin']);
     unset($_SESSION['timestamp_start']);
     unset($_SESSION['timestamp_last']);
     unset($_SESSION['tab_j_feries']);
     unset($_SESSION['config']);
     unset($_SESSION['lang']);
     session_destroy();
}



//
// formulaire de saisie du user/password
//
function session_saisie_user_password($erreur, $session_username, $session_password)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
    $config_php_conges_version      = $config->getInstalledVersion();
    $config_url_site_web_php_conges = $config->getUrlAccueil();

    $return_url                     = getpost_variable('return_url', false);

    // verif si on est dans le repertoire install
    if (substr(dirname ($_SERVER["SCRIPT_FILENAME"]), -6, 6) == "config") {  // verif si on est dans le repertoire install
        $config_dir=true;
    } else {
        $config_dir=false;
    }

    $add = '<script language="JavaScript" type="text/javascript">
<!--
// Les cookies sont obligatoires
if (! navigator.cookieEnabled) {
    document.write("<font color=\'#FF0000\'><br><br><center>'. _('cookies_obligatoires') .'</center></font><br><br>");
}
//-->
</script>
<noscript>
        <font color="#FF0000"><br><br><center>'. _('javascript_obligatoires') .'</center></font><br><br>
</noscript>';

    header_login('', $add);
    include_once TEMPLATE_PATH . 'login_form.php';

    bottom();
    exit;
}

//
// authentifie un user dans le base mysql avec son login et son passwd conges :
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
//
function authentification_passwd_conges($username, $password) : string
{
    $sql = \includes\SQL::singleton();
    if (isAuthentifiedNouvelAlgo($sql, $username, $password)) {
        return $username;
    } elseif (isAuthentifiedVieilAlgo($sql, $username, $password)) {
        if (updateUtilisateurChiffrement($sql, $username, $password)) {
            return $username;
        }

        throw new \Exception("Mise à jour algo impossible");
    }

    return '';
}

function isAuthentifiedNouvelAlgo(\includes\SQL $sql, string $username, string $password) : bool
{
    $req = 'SELECT u_passwd
    FROM conges_users
    WHERE u_login = "' . $sql->quote($username) . '"';
    $result = $sql->query($req);

    return password_verify($password, $result->fetch_array()['u_passwd']);
}

function isAuthentifiedVieilAlgo(\includes\SQL $sql, string $username, string $password) : bool
{
    $req = 'SELECT u_passwd
    FROM conges_users
    WHERE u_login = "' . $sql->quote($username) . '" AND u_passwd = "' . md5($password) . '"';
    $result = $sql->query($req);

    return $result->num_rows != 0;
}

function updateUtilisateurChiffrement(\includes\SQL $sql, string $username, string $password) : bool
{
    $req = 'UPDATE conges_users SET u_passwd = "' . password_hash($password, PASSWORD_BCRYPT) . '" WHERE u_login = "' . $sql->quote($username) . '" LIMIT 1';
    $sql->query($req);

    return $sql->affected_rows != 0;
}


// authentification du login/passwd sur un serveur LDAP
// - renvoie $username si authentification OK
// Renvoie toujours l'équivalent de OK car c'est l'API qui fait la connexion désormais
// @see vendor/libertempo/api/Tools/Services/LdapAuthentifierService.php
//
function authentification_ldap_conges($username, $password)
{
    return $username;
}


// Authentifie l'utilisateur auprès du serveur CAS, puis auprès de la base de donnée.
// Si le login qui a permis d'authentifier l'utilisateur auprès du serveur
//  CAS existe en tant que login d'une entrée de la table conges_user, alors
//  l'authentification est réussie et passwCAS renvoi le nom de l'utilisateur, "" sinon.
// - renvoie $username si authentification OK
// - renvoie ""        si authentification FAIL
function authentification_passwd_conges_CAS()
{
    $config_CAS_host       =$_SESSION['config']['CAS_host'];
    $config_CAS_portNumber =$_SESSION['config']['CAS_portNumber'];
    $config_CAS_URI        =$_SESSION['config']['CAS_URI'];
    $config_CAS_CACERT     =$_SESSION['config']['CAS_CACERT'];

    global $connexionCAS;
    global $logoutCas;


    \phpCAS::setDebug();

    // initialisation phpCAS
    if ($connexionCAS!="active") {
        $CASCnx = \phpCAS::client(CAS_VERSION_2_0,$config_CAS_host,$config_CAS_portNumber,$config_CAS_URI);
        $connexionCAS = "active";

    }

    if ($logoutCas==1) {
        \phpCAS::logout();
    }


    // Vérification SSL
    if (!empty($config_CAS_CACERT))
        \phpCAS::setCasServerCACert ($config_CAS_CACERT);
    else
        \phpCAS::setNoCasServerValidation();

    // authentificationCAS (redirection vers la page d'authentification de CAS)
    \phpCAS::forceAuthentication();

    $usernameCAS = \phpCAS::getUser();

    //On nettoie la session créée par phpCAS
    session_destroy();
    // On créé la session gérée par Libertempo
    session_create($usernameCAS);

    //ON VERIFIE ICI QUE L'UTILISATEUR EST DEJA ENREGISTRE SOUS DBCONGES
    $req_conges = 'SELECT u_login FROM conges_users WHERE u_login=\''. \includes\SQL::quote($usernameCAS).'\'';
    $res_conges = \includes\SQL::query($req_conges) ;
    $num_row_conges = $res_conges->num_rows;
    if ($num_row_conges !=0) {
        return $usernameCAS;
    } else {
        return '';
    }
}


function deconnexion_CAS($url = "")
{
    // import des parametres du serveur CAS

    $config_CAS_host       =$_SESSION['config']['CAS_host'];
    $config_CAS_portNumber =$_SESSION['config']['CAS_portNumber'];
    $config_CAS_URI        =$_SESSION['config']['CAS_URI'];

    global $connexionCAS;

    // initialisation phpCAS
    if ($connexionCAS!="active") {
        $CASCnx = \phpCAS::client(CAS_VERSION_2_0,$config_CAS_host,$config_CAS_portNumber,$config_CAS_URI);
        $connexionCAS = "active";

    }

    \phpCAS::logout();
}


function hash_user($user)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    $ics_salt = $config->getIcalSalt();
    $huser = hash('sha256', $user . $ics_salt);
    return $huser;
}

function unhash_user($huser_test)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    $user = "";
    $ics_salt = $config->getIcalSalt();
    $req_user = 'SELECT u_login FROM conges_users';
    $res_user = \includes\SQL::query($req_user) ;

    while ($resultat = $res_user->fetch_assoc()) {
        $clear_user = $resultat['u_login'];
        $huser = hash('sha256', $clear_user . $ics_salt);
        if ( $huser_test == $huser ) {
            $user = $clear_user;
        }
    }
    return $user;
}

function authentification_AD_SSO()
{
	$cred = explode('@',$_SERVER['REMOTE_USER']);
	if (count($cred)==1)
		$userAD = $cred[0];
	else
		$userAD = $cred[1];

	//ON VERIFIE ICI QUE L'UTILISATEUR EST DEJA ENREGISTRE SOUS DBCONGES
	$req_conges = 'SELECT u_login FROM conges_users WHERE u_login=\''. \includes\SQL::quote($userAD).'\'';
	$res_conges = \includes\SQL::query($req_conges) ;
	$num_row_conges = $res_conges->num_rows;
	if ($num_row_conges !=0)
		return $userAD;

	return '';
}

/**
 * Tente l'authentification auprès de l'API et stocke le token dans la session
 *
 * @param \App\Libraries\ApiClient $api Client de contact API
 * @param string $username
 * @param string $userPassword
 */
function storeTokenApi(\App\Libraries\ApiClient $apiClient, $username, $userPassword)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    if (in_array($config->getHowToConnectUser(), ['dbconges', 'ldap'], true)) {
        $dataUser = $apiClient->authentifyDbConges($username, $userPassword);
    } else {
        $dataUser = $apiClient->authentifyThirdParty($username);
    }


    $_SESSION['token'] = $dataUser['data'];
}

// calcule le nb de jours de conges à prendre pour un user entre 2 dates
// retourne le nb de jours  (opt_debut et opt_fin ont les valeurs "am" ou "pm"
function compter($user, $num_current_periode, $date_debut, $date_fin, $opt_debut, $opt_fin, &$comment, $num_update = null)
{
        $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
	$date_debut = convert_date($date_debut);
	$date_fin = convert_date($date_fin);

    $planningUser = \utilisateur\Fonctions::getUserPlanning($user);
    if (is_null($planningUser)) {
        $comment = _('aucun_planning_associe_utilisateur');
        return 0;
    }

	// verif si date_debut est bien anterieure à date_fin
	// ou si meme jour mais debut l'apres midi et fin le matin
	if ( (strtotime($date_debut) > strtotime($date_fin)) || ( ($date_debut==$date_fin) && ($opt_debut=="pm") && ($opt_fin=="am") ) )
	{
		$comment =  _('calcul_nb_jours_commentaire_bad_date') ;
		return 0 ;
	}


	if ( ($date_debut!=0) && ($date_fin!=0) )
	{
		// On ne peut pas calculer si, pour l'année considérée, les jours feries ont ete saisis
		if ( (verif_jours_feries_saisis($date_debut, $num_update)==false) || (verif_jours_feries_saisis($date_fin, $num_update)==false) ) {
			$comment =  _('calcul_impossible') ."<br>\n". _('jours_feries_non_saisis') ."<br>\n". _('contacter_rh') ."<br>\n" ;
			return 0 ;
		}


		/************************************************************/
		// 1 : on fabrique un tableau de jours (divisé chacun en 2 demi-jour) de la date_debut à la date_fin
		// 2 : on verifie que le conges demandé ne chevauche pas une periode deja posée
		// 3 : on affecte à 0 ou 1 chaque demi jour, en fonction de s'il est travaillé ou pas
		// 4 : à la fin , on parcours le tableau en comptant le nb de demi-jour à 1, on multiplie ce total par 0.5, ça donne le nb de jours du conges !

		$nb_jours=0;

		/************************************************************/
		// 1 : fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin
		$tab_periode_calcul = make_tab_demi_jours_periode($date_debut, $date_fin, $opt_debut, $opt_fin);


		/************************************************************/
		// 2 : on verifie que le conges demandé ne chevauche pas une periode deja posée
		if (verif_periode_chevauche_periode_user($date_debut, $date_fin, $user, $num_current_periode, $tab_periode_calcul, $comment, $num_update)) {
			return 0;
        }


		/************************************************************/
		// 3 : on affecte à 0 ou 1 chaque demi jour, en fonction de s'il est travaillé ou pas

		// on initialise le tableau global des jours fériés s'il ne l'est pas déjà :
		if (!isset($_SESSION["tab_j_feries"]))
		{
			init_tab_jours_feries();
		}
		// on initialise le tableau global des jours fermés s'il ne l'est pas déjà :
		if (!isset($_SESSION["tab_j_fermeture"]))
		{
			init_tab_jours_fermeture($user);
		}

		$current_day=$date_debut;
		$date_limite=jour_suivant($date_fin);

		// on va avancer jour par jour jusqu'à la date limite et voir si chaque jour est travaillé, férié, rtt, etc ...
		while ($current_day!=$date_limite)
		{
			// calcul du timestamp du jour courant
			if (substr_count($current_day,'/')){
				$pieces = explode("/", $current_day);  // date de la forme jj/mm/yyyy
				$y=$pieces[2];
				$m=$pieces[1];
				$j=$pieces[0];
			} else {
				$pieces = explode("-", $current_day);  // date de la forme yyyy-mm-dd
				$y=$pieces[0];
				$m=$pieces[1];
				$j=$pieces[2];
			}
			$timestamp_du_jour=mktime (0,0,0,$m,$j,$y);

			// on regarde si le jour est travaillé ou pas dans la config de l'appli
			$j_name=date("D", $timestamp_du_jour);
			if ( (($j_name=="Sat")&&(!$config->isSamediOuvrable())) || (($j_name=="Sun")&&(!$config->isDimancheOuvrable())))
			{
				// on ne compte ce jour à 0
				$tab_periode_calcul[$current_day]['am']=0;
				$tab_periode_calcul[$current_day]['pm']=0;
			} elseif (est_chome($timestamp_du_jour)) {
                 // verif si jour férié
				// on ne compte ce jour à 0
				$tab_periode_calcul[$current_day]['am']=0;
				$tab_periode_calcul[$current_day]['pm']=0;
			} else {
				/***************/
				// verif des rtt ou temp partiel (dans la table rtt)
				$val_matin="N";
				$val_aprem="N";
				recup_infos_artt_du_jour($user, $timestamp_du_jour, $val_matin, $val_aprem, $planningUser);

				if ($val_matin=="Y")  // rtt le matin
					$tab_periode_calcul[$current_day]['am']=0;

				if ($val_aprem=="Y") // rtt l'après midi
					$tab_periode_calcul[$current_day]['pm']=0;
			}

            $nb_jours = $nb_jours + $tab_periode_calcul[$current_day]['am'] + $tab_periode_calcul[$current_day]['pm'];

			$current_day=jour_suivant($current_day);
		}
		$nb_jours = $nb_jours * 0.5;
		verif_saisie_decimal($nb_jours);
		return $nb_jours;
	}
	else
		return 0;
}

// renvoit le jour suivant de la date passée en paramètre sous la forme jj/mm/yyyy
function jour_suivant($date)
{
	if (substr_count($date,'/')){
		$pieces = explode("/", $date);  // date de la forme jj/mm/yyyy
		$y=$pieces[2];
		$m=$pieces[1];
		$j=$pieces[0];
		$lendemain = date("d/m/Y", mktime(0, 0, 0, $m , $j+1, $y) );
	} else {
		$pieces = explode("-", $date);  // date de la forme yyyy-mm-dd
		$y=$pieces[0];
		$m=$pieces[1];
		$j=$pieces[2];
		$lendemain = date("Y-m-d", mktime(0, 0, 0, $m , $j+1, $y) );
	}

	return $lendemain;
}

// verifie si les jours fériés de l'annee de la date donnée sont enregistrés
// retourne TRUE ou FALSE
function verif_jours_feries_saisis($date)
{
	// on calcule le premier de l'an et le dernier de l'an de l'année de la date passee en parametre
	if (substr_count($date,'/'))
	{
		$tab_date=explode("/", $date); // date est de la forme dd/mm/YYYY
		$an=$tab_date[2];
	}
	if (substr_count($date,'-'))
	{
		$tab_date=explode("-", $date); // date est de la forme yyyy-mm-dd
		$an=$tab_date[0];
	}
	$premier_an="$an-01-01";
	$dernier_an="$an-12-31";

	$sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date >= "'.\includes\SQL::quote($premier_an).'" AND jf_date <= "'. \includes\SQL::quote($dernier_an).'" ';
	$res_select = \includes\SQL::query($sql_select);

	return ($res_select->num_rows != 0);
}

// fabrication et initialisation du tableau des demi-jours de la date_debut à la date_fin d'une periode
function make_tab_demi_jours_periode($date_debut, $date_fin, $opt_debut, $opt_fin)
{
	$tab_periode_calcul= [];

	// on va avancer jour par jour jusqu'à la date limite
	$current_day=$date_debut;
	$date_limite=jour_suivant($date_fin);
	while($current_day!=$date_limite)
	{
		$jour['am']=1;
		$jour['pm']=1;
		$tab_periode_calcul[$current_day]=$jour;
		$current_day=jour_suivant($current_day);
	}
	// attention au premier et dernier jour :
	if ($opt_debut=="pm")
		$tab_periode_calcul[$date_debut]['am']=0;
	if ($opt_fin=="am")
		$tab_periode_calcul[$date_fin]['pm']=0;
	return $tab_periode_calcul;
}

// verifie si la periode donnee chevauche une periode de conges d'un user donné
// attention à ne pas verifer le chevauchement avec la periode qu on est en train de traiter (si celle ci a déjà un num_periode)
// retourne TRUE si chevauchement et FALSE sinon !
function verif_periode_chevauche_periode_user($date_debut, $date_fin, $user, $num_current_periode = '', $tab_periode_calcul, &$comment, $num_update = null)
{

	/************************************************************/
	// 2 : on verifie que le conges demandé ne chevauche pas une periode deja posée
	// -> on recupere les periodes par rapport aux dates, on en fait une tableau de 1/2 journees, et on compare par 1/2 journee
	$tab_periode_deja_prise= [];
	$current_day=$date_debut;
	$date_limite=jour_suivant($date_fin);

	// on va avancer jour par jour jusqu'à la date limite et recupere les periodes qui contiennent ce jour...
	// on construit un tableau par date et 1/2 jour avec l'état de la periode
	while($current_day!=$date_limite)
	{
		$tab_periode_deja_prise[$current_day]['am']="no" ;
		$tab_periode_deja_prise[$current_day]['pm']="no" ;

		if ($num_update === null)
		{
			// verif si c'est deja un conges
			$user_periode_sql = 'SELECT  p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_etat
						FROM conges_periode
						WHERE p_login = "'.\includes\SQL::quote($user).'" AND ( p_etat=\'ok\' OR p_etat=\'valid\' OR p_etat=\'demande\' )
						'.(!empty($num_current_periode) ? 'AND p_num != '.intval($num_current_periode).' ' : '') .'
						AND p_date_deb<="'.\includes\SQL::quote($current_day).'" AND p_date_fin>="'.\includes\SQL::quote($current_day).'" ';
		}
		else
		{
			// verif si c'est deja un conges
			$user_periode_sql = 'SELECT  p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_etat
						FROM conges_periode
						WHERE p_login = "'.\includes\SQL::quote($user).'" AND ( p_etat=\'ok\' OR p_etat=\'valid\' OR p_etat=\'demande\' )
						'.(!empty($num_current_periode) ? 'AND p_num != '.intval($num_current_periode).' ' : '') .'
						AND p_date_deb<="'.\includes\SQL::quote($current_day).'" AND p_date_fin>="'. \includes\SQL::quote($current_day).'"
						AND p_num != \''.intval($num_update).'\' ';
		}

		$user_periode_request = \includes\SQL::query($user_periode_sql);

		if ($user_periode_request->num_rows !=0)  // le jour courant est dans une periode de conges du user
		{
			while ($resultat_periode=$user_periode_request->fetch_array())
			{
				$sql_p_date_deb=$resultat_periode["p_date_deb"];
				$sql_p_date_fin=$resultat_periode["p_date_fin"];
				$sql_p_demi_jour_deb=$resultat_periode["p_demi_jour_deb"];
				$sql_p_demi_jour_fin=$resultat_periode["p_demi_jour_fin"];
				$sql_p_etat=$resultat_periode["p_etat"];

				if ( ($current_day!=$sql_p_date_deb) && ($current_day!=$sql_p_date_fin) )
				{
					// pas la peine d'aller + loin, on chevauche une periode de conges !!!
					if ($sql_p_etat=="demande")
							$comment =  _('calcul_nb_jours_commentaire_impossible') ;
						else
							$comment =  _('calcul_nb_jours_commentaire') ;

					return true ;
				}
				elseif ( ($current_day==$sql_p_date_deb) && ($current_day==$sql_p_date_fin) ) // periode sur une seule journee
				{
					if ($sql_p_demi_jour_deb=="am")
						$tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
					if ($sql_p_demi_jour_fin=="pm")
						$tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
				}
				elseif ($current_day==$sql_p_date_deb)
				{
					if ($sql_p_demi_jour_deb=="am")
					{
						$tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
						$tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
					}
					else // alors ($sql_p_demi_jour_deb=="pm")
						$tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
				}
				else // alors ($current_day==$sql_p_date_fin)
				{
					if($sql_p_demi_jour_fin=="pm")
					{
						$tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
						$tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
					}
					else // alors ($sql_p_demi_jour_fin=="am")
						$tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
				}
			}
		}
		$current_day=jour_suivant($current_day);
	}// fin du while
	/**********************************************/
	// Ensuite verifie en parcourant le tableau qu'on vient de crée (s'il n'est pas vide)
    $donneesPeriodeDebut = $tab_periode_calcul[$date_debut];
    $donneesPeriodeFin = $tab_periode_calcul[$date_fin];
    if (1 == $donneesPeriodeDebut['am']) {
        $periodeDebut = (1 == $donneesPeriodeDebut['pm'])
            ? \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN_APRES_MIDI
            : \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN;
    } elseif (1 == $donneesPeriodeDebut['pm']) {
        $periodeDebut = \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI;
    }

    if (1 == $donneesPeriodeFin['pm']) {
        $periodeFin = (1 == $donneesPeriodeFin['am'])
            ? \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN_APRES_MIDI
            : \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI;
    } elseif (1 == $donneesPeriodeFin['am']) {
        $periodeFin = \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN;
    }

    $conge = new \App\ProtoControllers\Employe\Conge();
    if ($conge->isChevauchement($user, $date_debut, $periodeDebut, $date_fin, $periodeFin)) {
        $comment =  _('demande_heure_chevauche_demande');
        return true;
    }

	if (count($tab_periode_deja_prise)!=0)
	{
		$current_day=$date_debut;
		$date_limite=jour_suivant($date_fin);
		// on va avancer jour par jour jusqu'à la date limite et recupere les periodes qui contiennent ce jour...
		// on construit un tableau par date et 1/2 jour avec l'état de la periode
		while ($current_day!=$date_limite)
		{
			if ( ($tab_periode_calcul[$current_day]['am']==1) && ($tab_periode_deja_prise[$current_day]['am']!="no") )
			{
				// pas la peine d'aller + loin, on chevauche une periode de conges !!!
				if ($tab_periode_deja_prise[$current_day]['am']=="demande")
					$comment =  _('calcul_nb_jours_commentaire_impossible') ;
				else
					$comment =  _('calcul_nb_jours_commentaire') ;
				return true;
			}
			if ( ($tab_periode_calcul[$current_day]['pm']==1) && ($tab_periode_deja_prise[$current_day]['pm']!="no") )
			{
				// pas la peine d'aller + loin, on chevauche une periode de conges !!!
				if ($tab_periode_deja_prise[$current_day]['pm']=="demande")
					$comment =  _('calcul_nb_jours_commentaire_impossible') ;
				else
					$comment =  _('calcul_nb_jours_commentaire') ;

				return true;
			}
			$current_day=jour_suivant($current_day);
		}// fin du while
	}

    return false;
}
