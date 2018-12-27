<?php
include_once INCLUDE_PATH . 'lang_profile.php';

use PHPMailer\PHPMailer\PHPMailer;

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


function header_popup($title = '', $additional_head = '') {
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

function header_error($additional_head = '') {
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

function header_menu($info, $title = '', $additional_head = '') {
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
function session_saisie_user_password($erreur, $session_username)
{
    $PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);
    header_login('');
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
    if ((strtotime($date_debut) > strtotime($date_fin)) || ($date_debut == $date_fin && $opt_debut == "pm" && $opt_fin=="am")) {
        $comment =  _('calcul_nb_jours_commentaire_bad_date') ;
        return 0 ;
    }


    if ($date_debut != 0 && $date_fin != 0) {
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
        if (!isset($_SESSION["tab_j_feries"])) {
            init_tab_jours_feries();
        }
        // on initialise le tableau global des jours fermés s'il ne l'est pas déjà :
        if (!isset($_SESSION["tab_j_fermeture"])) {
            init_tab_jours_fermeture($user);
        }

        $current_day=$date_debut;
        $date_limite=jour_suivant($date_fin);

        // on va avancer jour par jour jusqu'à la date limite et voir si chaque jour est travaillé, férié, rtt, etc ...
        while ($current_day!=$date_limite)
        {
            // calcul du timestamp du jour courant
            if (substr_count($current_day,'/')) {
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

                if ($val_aprem=="Y") {
                    // rtt l'après midi
                    $tab_periode_calcul[$current_day]['pm']=0;
                }
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
    if (substr_count($date,'/')) {
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
    if (substr_count($date,'/')) {
        $tab_date=explode("/", $date); // date est de la forme dd/mm/YYYY
        $an=$tab_date[2];
    }
    if (substr_count($date,'-')) {
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
    if ($opt_debut=="pm") {
        $tab_periode_calcul[$date_debut]['am']=0;
    }
    if ($opt_fin=="am") {
        $tab_periode_calcul[$date_fin]['pm']=0;
    }

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

        if ($num_update === null) {
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

        if ($user_periode_request->num_rows !=0) {
            // le jour courant est dans une periode de conges du user
            while ($resultat_periode=$user_periode_request->fetch_array()) {
                $sql_p_date_deb=$resultat_periode["p_date_deb"];
                $sql_p_date_fin=$resultat_periode["p_date_fin"];
                $sql_p_demi_jour_deb=$resultat_periode["p_demi_jour_deb"];
                $sql_p_demi_jour_fin=$resultat_periode["p_demi_jour_fin"];
                $sql_p_etat=$resultat_periode["p_etat"];

                if ( ($current_day!=$sql_p_date_deb) && ($current_day!=$sql_p_date_fin) ) {
                    // pas la peine d'aller + loin, on chevauche une periode de conges !!!
                    if ($sql_p_etat=="demande") {
                        $comment =  _('calcul_nb_jours_commentaire_impossible') ;
                    } else {
                        $comment =  _('calcul_nb_jours_commentaire') ;
                    }

                    return true ;
                }
                elseif ( ($current_day==$sql_p_date_deb) && ($current_day==$sql_p_date_fin) ) {
                     // periode sur une seule journee
                    if ($sql_p_demi_jour_deb=="am") {
                        $tab_periode_deja_prise[$current_day]['am']="$sql_p_etat" ;
                    }
                    if ($sql_p_demi_jour_fin=="pm") {
                        $tab_periode_deja_prise[$current_day]['pm']="$sql_p_etat" ;
                    }
                } elseif ($current_day==$sql_p_date_deb) {
                    if ($sql_p_demi_jour_deb=="am") {
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

// retourne le nom du jour de la semaine en francais sur 2 caracteres
function get_j_name_fr_2c($timestamp)
{
    $jour_name_fr_2c=array(0=>'di',1=>'lu', 2=>'ma',3=>'me',4=>'je',5=>'ve',6=>'sa',);
    $jour_num=date('w', $timestamp);
    if (isset($jour_name_fr_2c[$jour_num]))
        return $jour_name_fr_2c[$jour_num];
    else
        return false;
}

function saisie_nouveau_conges2($user_login, $year_calendrier_saisie_debut, $mois_calendrier_saisie_debut, $year_calendrier_saisie_fin, $mois_calendrier_saisie_fin, $onglet)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

    $PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
    $new_date_fin = date('d/m/Y');
    $return = '';

    $return .= '<form NAME="dem_conges" action="' . $PHP_SELF . '?onglet=' . $onglet . '" method="POST">
        <div class="row">
        <div class="col-md-6">
        <div class="form-inline">';
    $return .= '<div class="form-group"><label for="new_deb">' . _('divers_date_debut') . '</label><input type="text" class="form-control date" name="new_debut" value="' . $new_date_fin . '"></div>';

    $return .= '<input type="radio" name="new_demi_jour_deb" ';

    // attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
    if ((isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE)) {
        $return .= 'onClick="compter_jours();return true;" ';
    } else {
        $return .= 'onChange="compter_jours();return false;" ';
    }
    $return .= 'value="am" checked>&nbsp;' .  _('form_debut_am') . '&nbsp;';
    $return .= '<input type="radio" name="new_demi_jour_deb" ';

    if ((isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE)) {
        $return .= 'onClick="compter_jours();return true;" ';
    } else {
        $return .= 'onChange="compter_jours();return false;" ';
    }

    $return .= 'value="pm">&nbsp;' .  _('form_debut_pm');
    $return .= '</div>';
    $return .= '</div>';
    $return .= '<div class="col-md-6">';
    $return .= '<div class="form-inline">';
    $return .= '<div class="form-group">';
    $return .= '<label for="new_fin">' . _('divers_date_fin') . '</label><input type="text" class="form-control date" name="new_fin" value="' . $new_date_fin . '">';
    $return .= '</div>';
    $return .= '<input type="radio" name="new_demi_jour_fin" ';

    // attention : IE6 : bug avec les "OnChange" sur les boutons radio!!! (on remplace par OnClick)
    if ((isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE)) {
        $return .= 'onClick="compter_jours();return true;" ' ;
    } else {
        $return .= 'onChange="compter_jours();return false;" ' ;
    }
    $return .= 'value="am">&nbsp;'. _('form_fin_am') . '&nbsp;';
    $return .= '<input class="form-controm" type="radio" name="new_demi_jour_fin" ';

    if ((isset($_SERVER['HTTP_USER_AGENT'])) && (stristr($_SERVER['HTTP_USER_AGENT'], 'MSIE')!=FALSE)) {
        $return .= 'onClick="compter_jours();return true;" ' ;
    } else {
        $return .= 'onChange="compter_jours();return false;" ' ;
    }

    $return .= 'value="pm" checked>&nbsp;' . _('form_fin_pm');
    $return .= '</div>';
    $return .= '</div>';
    $return .= '</div>';
    $return .= '<br />';
    $return .= '<label>' . _('saisie_conges_nb_jours') .'&nbsp</label>';
    $return .= '<span id="new_nb_jours"></span>';
    $return .= '<hr/>';

    /*****************/
    /* boutons radio */
    /*****************/
    // recup du tableau des types de conges
    $tab_type_conges=recup_tableau_types_conges();
    // recup du tableau des types d'absence
    $tab_type_absence=recup_tableau_types_absence();
    // recup d tableau des types de conges exceptionnels
    $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
    $already_checked = false;

    $return .= '<div class="row type-conges">';
    // si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
    // OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
    // OU si le user est un RH ou un admin
    if (( $config->canUserSaisieDemande() && $user_login==$_SESSION['userlogin'] ) || ( !$config->canUserSaisieDemande() && $user_login!=$_SESSION['userlogin'] ) || is_hr($_SESSION['userlogin']) || is_admin($_SESSION['userlogin']))
    {
        // congés
        $return .= '<div class="col-md-4">';
        $return .= '<label>' . _('divers_conges') . '</label>';
        foreach ($tab_type_conges as $id => $libelle) {
            if ($id==1) {
                $return .= '<input type="radio" name="new_type" value="' . $id . '" checked>'. $libelle . '<br>';
                $already_checked = true;
            } else {
                $return .= '<input type="radio" name="new_type" value="' . $id . '">' . $libelle . '<br>';
            }
        }
        $return .= '</div>';
    }

    // si le user a droit de saisir une mission ET si on est PAS dans une fenetre de responsable
    // OU si le resp a droit de saisir une mission ET si on est PAS dans une fenetre dd'utilisateur
    // OU si le resp a droit de saisir une mission ET si le resp est resp de lui meme
    if ((($config->canUserSaisieMission())&&($user_login==$_SESSION['userlogin']))
            || (($config->canResponsableSaisieMission())&&($user_login!=$_SESSION['userlogin']))
            || (($config->canResponsableSaisieMission()) && (\App\ProtoControllers\Responsable::isRespDeUtilisateur($_SESSION['userlogin'] , $user_login)))) {
        // absences
        $return .= '<div class="col-md-4">';
        $return .= '<label>' . _('divers_absences') . '</label>';
        foreach($tab_type_absence as $id => $libelle) {
            if (!$already_checked) {
                $return .= '<input type="radio" name="new_type" value="' . $id . '" checked>' . $libelle . '<br>';
                $already_checked = true;
            } else {
                $return .= '<input type="radio" name="new_type" value="' . $id . '">' . $libelle . '<br>';
            }
        }
        $return .= '</div>';
    }

    // si le user a droit de saisir une demande de conges ET si on est PAS dans une fenetre de responsable
    // OU si le user n'a pas droit de saisir une demande de conges ET si on est dans une fenetre de responsable
    if (($config->isCongesExceptionnelsActive()) && ((($config->canUserSaisieDemande())&&($user_login==$_SESSION['userlogin'])) || ((!$config->canUserSaisieDemande())&&($user_login!=$_SESSION['userlogin'])))) {
        // congés exceptionnels
        $return .= '<div class="col-md-4">';
        $return .= '<label>' . _('divers_conges_exceptionnels') . '</label>';
        foreach ($tab_type_conges_exceptionnels as $id => $libelle) {
            if ($id == 1) {
                $return .= '<input type="radio" name="new_type" value="' . $id . '" checked> ' . $libelle . '<br>';
            } else {
                $return .= '<input type="radio" name="new_type" value="' . $id . '">' . $libelle . '<br>';
            }
        }
        $return .= '</div>';
    }

    $return .= '</div>';
    $return .= '<hr/>';
    $return .= '<label>' . _('divers_comment_maj_1') . '</label><input class="form-control" type="text" name="new_comment" size="25" maxlength="30" value="">';


    // zones de texte

    $return .= '<p id="comment_nbj" style="color:red">&nbsp;</p>';
    $return .= '<br>';
    $return .= '<input type="hidden" name="user_login" value="' . $user_login . '">';
    $return .= '<input type="hidden" name="new_demande_conges" value=1>';
    // boutons du formulaire
    $return .= '<input type="submit" class="btn btn-success" value="' . _('form_submit') . '">';
    $return .= '<a class="btn" href="' . $PHP_SELF . '">' . _('form_cancel') . '</a>';
    $return .= '</form>';
    return $return;
}

// initialisation des variables pour la navigation mois précédent / mois suivant
// certains arguments sont passés par référence (avec &) car on change leur valeur
function init_var_navigation_mois_year( $mois_calendrier_saisie_debut, $year_calendrier_saisie_debut, &$mois_calendrier_saisie_debut_prec, &$year_calendrier_saisie_debut_prec,    &$mois_calendrier_saisie_debut_suiv, &$year_calendrier_saisie_debut_suiv, $mois_calendrier_saisie_fin, $year_calendrier_saisie_fin, &$mois_calendrier_saisie_fin_prec, &$year_calendrier_saisie_fin_prec, &$mois_calendrier_saisie_fin_suiv, &$year_calendrier_saisie_fin_suiv )
{
    if ($mois_calendrier_saisie_debut==1) {
        $mois_calendrier_saisie_debut_prec=12;
        $year_calendrier_saisie_debut_prec=$year_calendrier_saisie_debut-1 ;
    }
    else
    {
        $mois_calendrier_saisie_debut_prec=$mois_calendrier_saisie_debut-1 ;
        $year_calendrier_saisie_debut_prec=$year_calendrier_saisie_debut ;
    }
    if ($mois_calendrier_saisie_debut==12) {
        $mois_calendrier_saisie_debut_suiv=1;
        $year_calendrier_saisie_debut_suiv=$year_calendrier_saisie_debut+1 ;
    }
    else
    {
        $mois_calendrier_saisie_debut_suiv=$mois_calendrier_saisie_debut+1 ;
        $year_calendrier_saisie_debut_suiv=$year_calendrier_saisie_debut ;
    }

    if ($mois_calendrier_saisie_fin==1) {
        $mois_calendrier_saisie_fin_prec=12;
        $year_calendrier_saisie_fin_prec=$year_calendrier_saisie_fin-1 ;
    }
    else
    {
        $mois_calendrier_saisie_fin_prec=$mois_calendrier_saisie_fin-1 ;
        $year_calendrier_saisie_fin_prec=$year_calendrier_saisie_fin ;
    }
    if ($mois_calendrier_saisie_fin==12) {
        $mois_calendrier_saisie_fin_suiv=1;
        $year_calendrier_saisie_fin_suiv=$year_calendrier_saisie_fin+1 ;
    }
    else
    {
        $mois_calendrier_saisie_fin_suiv=$mois_calendrier_saisie_fin+1 ;
        $year_calendrier_saisie_fin_suiv=$year_calendrier_saisie_fin ;
    }
}


// affiche une chaine représentant un decimal sans 0 à la fin ...
// (un point separe les unités et les decimales et on ne considere que 2 decimales !!!)
// ex : 10.00 devient 10  , 5.50 devient 5.5  , et 3.05 reste 3.05
function affiche_decimal($str)
{
    $champs=explode('.', $str);
    $int=$champs[0];
    $decimal='00';
    if (count($champs)>1) {
        $decimal = $champs[1];
    }
    if ($decimal=='00') {
        return $int ;
    }
    if (preg_match('/[0-9][1-9]$/' , $decimal )) {
        return $str;
    }
    if (preg_match('/([0-9]?)0?$/' , $decimal, $regs )) {
        return $int.'.'.$regs[1] ;
    }
    echo 'ERREUR: affiche_decimal('.$str.') : '.$str.' n\'a pas le format attendu !!!!<br>';
    exit;
}

// verif validité des valeurs saisies lors d'une demande de conges par un user ou d'une saisie de conges par le responsable
//  (attention : le $new_nb_jours est passé par référence car on le modifie si besoin)
function verif_saisie_new_demande($new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, &$new_nb_jours, $new_comment, $login)
{
    $verif = true;

    // leur champs doivent etre renseignés dans le formulaire
    if ($new_debut == '' || $new_fin == '' || $new_nb_jours == '') {
        echo '<br>'. _('verif_saisie_erreur_valeur_manque') .'<br>';
        $verif = false;
    }

    if (!preg_match('/([0-9]+)([\.\,]*[0-9]{1,2})*$/', $new_nb_jours)) {
        echo '<br>'. _('verif_saisie_erreur_nb_jours_bad') .'<br>';
        $verif = false;
    } elseif (preg_match('/([0-9]+)\,([0-9]{1,2})$/', $new_nb_jours, $reg)) {
        $new_nb_jours=$reg[1].'.'.$reg[2]; // on remplace la virgule par un point pour les décimaux
    }

    // si la date de fin est antérieure à la date debut
    if (strnatcmp($new_debut, $new_fin)>0) {
        echo '<br>'. _('verif_saisie_erreur_fin_avant_debut') .'<br>';
        $verif = false;
    }

    // si la date debut et fin = même jour mais début=après midi et fin=matin !!
    if ( $new_debut == $new_fin && $new_demi_jour_deb=='pm' && $new_demi_jour_fin == 'am' ) {
        echo '<br>'. _('verif_saisie_erreur_debut_apres_fin') .'<br>';
        $verif = false;
    }

    // Ensuite verifie en parcourant le tableau qu'on vient de crée (s'il n'est pas vide)
    if ('am' == $new_demi_jour_deb) {
        $periodeDebut = \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN;
    } else {
        $periodeDebut = \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI;
    }

    if ('am' == $new_demi_jour_fin) {
        $periodeFin = \App\Models\Planning\Creneau::TYPE_PERIODE_MATIN;
    } else {
        $periodeFin = \App\Models\Planning\Creneau::TYPE_PERIODE_APRES_MIDI;
    }

    $conge = new \App\ProtoControllers\Employe\Conge();
    if ($conge->isChevauchement($login, $new_debut, $periodeDebut, $new_fin, $periodeFin)) {
        echo '<br>'. _('demande_heure_chevauche_demande') .'<br>';
        $verif = false;
    }

    $tab_periode_calcul = make_tab_demi_jours_periode($new_debut, $new_fin, $new_demi_jour_deb, $new_demi_jour_fin);
    if (verif_periode_chevauche_periode_user($new_debut, $new_fin, $login, "", $tab_periode_calcul, $new_comment)) {
        echo '<br>'._('calcul_nb_jours_commentaire') .'<br>';
        $verif = false;
    }

    $new_comment = htmlentities($new_comment, ENT_QUOTES | ENT_HTML401);

    return $verif;
}


// renvoit la class de cellule du jour indiquée par le timestamp
// (une classe pour les jours de semaine et une pour les jours de week end)
function get_td_class_of_the_day_in_the_week($timestamp_du_jour)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    $j_name = date('D', $timestamp_du_jour);
    $j_date = date('Y-m-d', $timestamp_du_jour);

    if (($j_name=='Sat' && !$config->isSamediOuvrable()) || ($j_name=='Sun' && !$config->isDimancheOuvrable()) || est_chome($timestamp_du_jour) || est_ferme($timestamp_du_jour))
        return 'weekend';
    else
        return 'semaine';
}


// recup des infos ARTT ou Temps Partiel :
// attention : les param $val_matin et $val_aprem sont passées par référence (avec &) car on change leur valeur
function recup_infos_artt_du_jour($sql_login, $j_timestamp, &$val_matin, &$val_aprem, array $planningUser)
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);
    $num_semaine = date('W', $j_timestamp);
    $jour_name_fr_2c = get_j_name_fr_2c($j_timestamp); // nom du jour de la semaine en francais sur 2 caracteres

    // on ne cherche pas d'artt les samedis ou dimanches quand il ne sont pas travaillés (cf config de php_conges)
    if (($jour_name_fr_2c != 'sa' || $config->isSamediOuvrable())  && ( $jour_name_fr_2c != 'di' || $config->isDimancheOuvrable())) {
        // verif si le jour fait l'objet d'un echange ....
        $date_j            = date('Y-m-d', $j_timestamp);
        $sql_echange_rtt = 'SELECT e_absence FROM conges_echange_rtt WHERE e_login="'. $db->quote($sql_login).'" AND e_date_jour="'. $db->quote($date_j).'" ';
        $res_echange_rtt = $db->query($sql_echange_rtt);
        $num_echange_rtt = $res_echange_rtt->num_rows;
        // si le jour est l'objet d'un echange, on tient compte de l'échange
        if ( $num_echange_rtt != 0 ) {
            $result_echange_rtt = $res_echange_rtt->fetch_array();
            if ( in_array($result_echange_rtt['e_absence'] , array( 'J' , 'M') ) )
                $val_matin = 'Y';
            else
                $val_matin = 'N';
            if ( in_array($result_echange_rtt['e_absence'] , array( 'J' , 'A') ) )
                $val_aprem = 'Y';
            else
                $val_aprem = 'N';
        } else {
            /* Sinon, on s'appuie sur le planning normalement */
            $realWeekType = \utilisateur\Fonctions::getRealWeekType($planningUser, $num_semaine);
            if (NIL_INT === $realWeekType) {
                /* Si la semaine n'est pas travaillée */
                $val_matin = 'Y';
                $val_aprem = 'Y';
            } else {
                $planningWeek = $planningUser[$realWeekType];
                $jourId = date('N', $j_timestamp);
                if (!\utilisateur\Fonctions::isWorkingDay($planningWeek, $jourId)) {
                    /* Si le jour n'est pas travaillé */
                    $val_matin = 'Y';
                    $val_aprem = 'Y';
                } else {
                    /* Vérification si le créneau est travaillé */
                    $planningDay = $planningWeek[$jourId];
                    $val_matin = (\utilisateur\Fonctions::isWorkingMorning($planningDay)) ? 'N' : 'Y';
                    $val_aprem = (\utilisateur\Fonctions::isWorkingAfternoon($planningDay)) ? 'N' : 'Y';
                }
            }
        }
    }
}

// verif validité d'un nombre saisi (decimal ou non)
//  (attention : le $nombre est passé par référence car on le modifie si besoin)
function verif_saisie_decimal(&$nombre)
{
    $nombre = number_format(floatval($nombre), 1, '.', '' );
    return true;
}

// donne la date en francais (dans la langue voulue)(meme formats que la fonction PHP date() cf manuel php)
function date_fr($code, $timestmp)
{
    $les_mois_longs  = array('pas_de_zero',  _('janvier') ,  _('fevrier') ,  _('mars') ,  _('avril') , _('mai') ,  _('juin') ,  _('juillet') ,  _('aout') , _('septembre') ,  _('octobre') ,  _('novembre') ,  _('decembre') );
    $les_jours_longs  = array( _('dimanche') ,  _('lundi') ,  _('mardi') ,  _('mercredi') , _('jeudi') ,  _('vendredi') ,  _('samedi') );
    $les_jours_courts = array( _('dimanche_short') ,  _('lundi_short') ,  _('mardi_short') , _('mercredi_short') ,  _('jeudi_short') ,  _('vendredi_short') ,  _('samedi_short') );

    switch ($code) {
        case 'F':
            return $les_mois_longs[ date('n', $timestmp) ];
            break;

        case 'l':
            return $les_jours_longs[ date('w', $timestmp) ];
            break;

        case 'D':
            return $les_jours_courts[ date('w', $timestmp) ];
            break;

        default:
            return date($code, $timestmp);
            break;
    }
}



// envoi d'un message d'avertissement
// parametre 1=login de l'expéditeur
// parametre 2=login du destinataire (ou ":responsable:" si envoi au(x) responsable(s))
// parametre 3= numero de l'absence concernée
// parametre 4=objet du message (cf table conges_mail pour les diff valeurs possibles)
function alerte_mail($login_expediteur, $destinataire, $num_periode, $objet)
{

    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    /*********************************************/
    // recup des infos concernant l'expéditeur ....
    $mail_array        = find_email_adress_for_user($login_expediteur);
    $mail_sender_name    = $mail_array[0];
    $mail_sender_addr    = $mail_array[1];

    /*********************************************/
    // recherche des infos concernant le destinataire ...
    // recherche du login du (des) destinataire(s) dans la base
    $dest_mail  = '';
    if ($destinataire == ':responsable:')  // c'est un message au responsable
    {
        $tab_resp   = get_tab_resp_du_user($login_expediteur);
        foreach($tab_resp as $item_login => $item_presence)
        {
            // recherche de l'adresse mail du (des) responsable(s) :
            $mail_array_dest = find_email_adress_for_user($item_login);
            $mail_dest_name = $mail_array_dest[0];
            $mail_dest_addr = $mail_array_dest[1];

            if ($mail_dest_addr == '')
                echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
            else
            {
                // on change l'objet si c'est un "new_demande" à un resp absent et qu'on gere les absence de resp !
                if ($config->isGestionResponsableAbsent() && $item_presence == 'absent' && $objet == 'new_demande')
                    $new_objet  = 'new_demande_resp_absent';
                else
                    $new_objet  = $objet;

                constuct_and_send_mail($new_objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode);
            }
        }

    } else {  // c'est un message du responsale à un user
        $dest_login        = $destinataire ;
        $mail_array_dest    = find_email_adress_for_user($dest_login);
        $mail_dest_name     = $mail_array_dest[0];
        $mail_dest_addr     = $mail_array_dest[1];

        if ($mail_dest_addr == '')
            echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
        else
            constuct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode);

        /****************************/
        if ($objet == 'valid_conges') {  // c'est un mail de première validation de demande : il faut faire une copie au(x) grand(s) responsable(s)
            // on recup la liste des grands resp du user
            $tab_grd_resp   = array();
            get_tab_grd_resp_du_user($dest_login, $tab_grd_resp);

            if (count($tab_grd_resp) != 0) {
                foreach($tab_grd_resp as $item_login) {
                    // recherche de l'adresse mail du (des) responsable(s) :
                    $mail_array_dest = find_email_adress_for_user($item_login);
                    $mail_dest_name = $mail_array_dest[0];
                    $mail_dest_addr = $mail_array_dest[1];

                    if ( $mail_dest_addr == '' )
                        echo "<b>ERROR : $mail_dest_name : no mail address !</b><br>\n";
                    else
                        constuct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode);
                }
            }
        }
    }
}


// construit et envoie le mail
function constuct_and_send_mail($objet, $mail_sender_name, $mail_sender_addr, $mail_dest_name, $mail_dest_addr, $num_periode)
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);
    /*********************************************/
    // init du mail
    $mail = new PHPMailer();

    if (file_exists(CONFIG_PATH .'config_SMTP.php')) {
        include CONFIG_PATH .'config_SMTP.php';

        if (!empty($config_SMTP_host)) {
            $mail->IsSMTP();
            $mail->Host = $config_SMTP_host;
            $mail->Port = $config_SMTP_port;

            if (!empty($config_SMTP_user)) {
                $mail->SMTPAuth = true;
                $mail->Username = $config_SMTP_user;
                $mail->Password = $config_SMTP_pwd;
            }
            if (!empty($config_SMTP_sec)) {
                $mail->SMTPSecure = $config_SMTP_sec;
            } else {
                 $mail->SMTPAutoTLS = false;
            }
        }
    } else {
        if (file_exists('/usr/sbin/sendmail'))
            $mail->IsSendmail();   // send message using the $Sendmail program
        elseif (file_exists('/var/qmail/bin/sendmail'))
            $mail->IsQmail(); // send message using the qmail MTA
        else
            $mail->IsMail(); // send message using PHP mail() function
    }

    // initialisation du langage utilisé par php_mailer
    $mail->SetLanguage( 'fr', ROOT_PATH . 'vendor/phpmailer/phpmailer/language/');
    $mail->FromName    = $mail_sender_name;
    $mail->From        = $mail_sender_addr;
    $mail->AddAddress($mail_dest_addr);

    /*********************************************/
    // recup des infos de l'absence
    if ($num_periode == "test") {
        // affiche : "23 / 01 / 2008 (am)"
        $sql_date_deb = "01 / 01 / 2001 (am)";
        // affiche : "23 / 01 / 2008 (am)"
        $sql_date_fin = "02 / 01 / 2001 (am)";
        $sql_nb_jours = 2;
        $sql_commentaire = "Test comment";
        $sql_type_absence = "cp";
        $mail->SMTPDebug = 3; // Much easier if something fails
    } else {
        $select_abs = 'SELECT conges_periode.p_date_deb,conges_periode.p_demi_jour_deb,conges_periode.p_date_fin,conges_periode.p_demi_jour_fin,conges_periode.p_nb_jours,conges_periode.p_commentaire,conges_type_absence.ta_libelle
                FROM conges_periode, conges_type_absence WHERE conges_periode.p_num='.$num_periode.' AND conges_periode.p_type = conges_type_absence.ta_id;';
        $res_abs = $db->query($select_abs);
        $rec_abs = $res_abs->fetch_array();
        $tab_date_deb = explode('-', $rec_abs['p_date_deb']);
        // affiche : "23 / 01 / 2008 (am)"
        $sql_date_deb = $tab_date_deb[2]." / ".$tab_date_deb[1]." / ".$tab_date_deb[0]." (".$rec_abs["p_demi_jour_deb"].")" ;
        $tab_date_fin= explode("-", $rec_abs["p_date_fin"]);
        // affiche : "23 / 01 / 2008 (am)"
        $sql_date_fin = $tab_date_fin[2]." / ".$tab_date_fin[1]." / ".$tab_date_fin[0]." (".$rec_abs["p_demi_jour_fin"].")" ;
        $sql_nb_jours = $rec_abs["p_nb_jours"];
        $sql_commentaire = $rec_abs["p_commentaire"];
        $sql_type_absence = $rec_abs["ta_libelle"];
    }

    /*********************************************/
    // construction des sujets et corps des messages
    if ($objet=="valid_conges") {
        $key1="mail_prem_valid_conges_sujet" ;
        $key2="mail_prem_valid_conges_contenu" ;
    } elseif ($objet=="accept_conges") {
        $key1="mail_valid_conges_sujet" ;
        $key2="mail_valid_conges_contenu" ;
    } else {
        $key1="mail_".$objet."_sujet" ;
        $key2="mail_".$objet."_contenu" ;
    }
    $sujet = $_SESSION['config'][$key1];
    $contenu = $_SESSION['config'][$key2];
    $contenu = str_replace("__URL_ACCUEIL_CONGES__", $config->getUrlAccueil(), $contenu);
    $contenu = str_replace("__SENDER_NAME__", $mail_sender_name, $contenu);
    $contenu = str_replace("__DESTINATION_NAME__", $mail_dest_name, $contenu);
    $contenu = str_replace("__NB_OF_DAY__", $sql_nb_jours, $contenu);
    $contenu = str_replace("__DATE_DEBUT__", $sql_date_deb, $contenu);
    $contenu = str_replace("__DATE_FIN__", $sql_date_fin, $contenu);
    $contenu = str_replace("__RETOUR_LIGNE__", "\r\n", $contenu);
    $contenu = str_replace("__COMMENT__", $sql_commentaire, $contenu);
    $contenu = str_replace("__TYPE_ABSENCE__", $sql_type_absence, $contenu);

    // construction du corps du mail
    $mail->Subject = stripslashes(utf8_decode($sujet ));
    $mail->Body = stripslashes(utf8_decode($contenu ));


    /*********************************************/
    // ENVOI du mail
    if (!isset($mail_dest_addr)) {
        echo "<b>ERROR : No recipient address for the message!</b><br>\n";
        echo "<b>Message was not sent </b><br>";
    } else {
        if (!$mail->Send()) {
            echo "<b>Message was not sent </b><br>";
            echo "<b>Mailer Error: " . $mail->ErrorInfo."</b><br>";
        }
    }
}



// recuperation du mail d'un user
// renvoit un tableau a 2 valeurs : prenom+nom et email
function find_email_adress_for_user($login)
{
    $found_mail = array();
    $infoUser = \App\ProtoControllers\Utilisateur::getDonneesUtilisateur($login);

    $found_mail[] = $infoUser['u_prenom'] . " " . strtoupper($infoUser['u_nom']);
    array_push($found_mail, $infoUser['u_email']) ;

    return $found_mail ;
}

// met la date aaaa-mm-jj dans le format jj-mm-aaaa
function eng_date_to_fr($une_date)
{
 return substr($une_date, 8)."-".substr($une_date, 5, 2)."-".substr($une_date, 0, 4);

}

// recup du nom d'un groupe grace à son group_id
function get_group_name_from_id($groupe_id)
{
    $db = \includes\SQL::singleton();
    $req_name='SELECT g_groupename FROM conges_groupe WHERE g_gid='. $db->quote($groupe_id);
    $ReqLog_name = $db->query($req_name);
    $resultat_name = $ReqLog_name->fetch_array();
    return $resultat_name["g_groupename"];
}

// recup de la liste de TOUS les users dont $resp_login est responsable
// (prend en compte le resp direct, les groupes, le resp virtuel, etc ...)
// renvoie une liste de login entre quotes et séparés par des virgules
function get_list_all_users_du_resp($resp_login)
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);

        $groupeIds = \App\ProtoControllers\Responsable::getIdGroupeResp($resp_login);
        $listUsers = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupeIds);
        $list_users="";
    $sql1="SELECT DISTINCT(u_login) FROM conges_users WHERE u_login!='$resp_login'";
        $sql1 .= ' AND u_login IN ("' . implode('","', $listUsers) . '")';

    $sql1 = $sql1." ORDER BY u_login " ;
    $ReqLog1 = $db->query($sql1);

    while ($resultat1 = $ReqLog1->fetch_array()) {
        $current_login=$resultat1["u_login"];
        if ($list_users=="")
            $list_users="'$current_login'";
        else
            $list_users=$list_users.", '$current_login'";
    }

    /************************************/
    // gestion des absence des responsables :
    // on recup la liste des users des resp absents, dont $resp_login est responsable
    if ($config->isGestionResponsableAbsent()) {
        // recup liste des resp absents, dont $resp_login est responsable
        $sql_2='SELECT DISTINCT(u_login) FROM conges_users WHERE u_is_resp=\'Y\' AND u_login!="'. $db->quote($resp_login).'"';
        $sql_2=$sql_2.' AND u_login IN ("' . implode('","', $listUsers) . '")';

        $sql_2 = $sql_2." ORDER BY u_login " ;

        $ReqLog_2 = $db->query($sql_2);

        // on va verifier si les resp récupérés sont absents (si oui, c'est $resp_login qui traite leurs users
        while ($resultat_2 = $ReqLog_2->fetch_array())
        {
            $current_resp=$resultat_2["u_login"];
            // verif dans la base si le current_resp est absent :
            $req = 'SELECT p_num FROM conges_periode WHERE p_login = "'. $db->quote($current_resp).'" AND p_etat = \'ok\' AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW()) AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())';
            $ReqLog_3 = $db->query($req);

            // si le current resp est absent : on recup la liste de ses users pour les traiter .....
            if ($ReqLog_3->num_rows!=0) {
                if ($list_users=="")
                    $list_users=get_list_all_users_du_resp($current_resp);
                else
                    $list_users=$list_users.", ".get_list_all_users_du_resp($current_resp);
            }
        }

    }
    // FIN gestion des absence des responsables :
    /************************************/
    return $list_users;
}

// recup de la liste des users d'un groupe donné
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_users_du_groupe($group_id)
{
    $list_users=array();
    $db = \includes\SQL::singleton();
    $sql1='SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid = '.intval($group_id).' ORDER BY gu_login ';
    $ReqLog1 = $db->query($sql1);
    while ($resultat1 = $ReqLog1->fetch_array())
        $list_users[] = "'". $db->quote($resultat1["gu_login"])."'";

    $list_users = implode(' , ', $list_users);
    return $list_users;
}

// recup de la liste des groupes dont $resp_login est responsable
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_resp($resp_login)
{
    $list_group="";
    $db = \includes\SQL::singleton();
    $sql1='SELECT gr_gid FROM conges_groupe_resp WHERE gr_login="'. $db->quote($resp_login).'" ORDER BY gr_gid';
    $ReqLog1 = $db->query($sql1);

    if ($ReqLog1->num_rows !=0)
    {
        while ($resultat1 = $ReqLog1->fetch_array())
        {
            $current_group=$resultat1["gr_gid"];
            if ($list_group=="")
                $list_group="$current_group";
            else
                $list_group=$list_group.", $current_group";
        }
    }
    return $list_group;
}

// recup de la liste des groupes dont $resp_login est grandresponsable
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_grand_resp($resp_login)
{
    $list_group="";
    $db = \includes\SQL::singleton();
    $sql1='SELECT ggr_gid FROM conges_groupe_grd_resp WHERE ggr_login="'.$db->quote($resp_login).'" ORDER BY ggr_gid';
    $ReqLog1 = $db->query($sql1);

    if ($ReqLog1->num_rows!=0)
    {
        while ($resultat1 = $ReqLog1->fetch_array())
        {
            $current_group=$resultat1["ggr_gid"];
            if ($list_group=="")
                $list_group="$current_group";
            else
                $list_group=$list_group.", $current_group";
        }
    }
    return $list_group;
}

// recup de la liste des groupes à double validation
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid()
{
    $list_groupes_double_valid="";
    $sql1="SELECT g_gid FROM conges_groupe WHERE g_double_valid='Y' ORDER BY g_gid ";
    $ReqLog1 = \includes\SQL::singleton()->query($sql1);

    while ($resultat1 = $ReqLog1->fetch_array())
    {
        $current_gid=$resultat1["g_gid"];
        if ($list_groupes_double_valid=="")
            $list_groupes_double_valid="$current_gid";
        else
            $list_groupes_double_valid=$list_groupes_double_valid.", $current_gid";
    }
    return $list_groupes_double_valid;

}

// recup de la liste des groupes à double validation, dont $resp_login est responsable
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid_du_resp($resp_login)
{
    $list_groupes_double_valid_du_resp="";
    $list_groups=get_list_groupes_du_resp($resp_login);
    $db = \includes\SQL::singleton();

    if ($list_groups!="") { // si $resp_login est responsable d'au moins un groupe
        $sql1='SELECT DISTINCT(g_gid) FROM conges_groupe WHERE g_double_valid=\'Y\' AND g_gid IN ('. $db->quote($list_groups).') ORDER BY g_gid ';
        $ReqLog1 = $db->query($sql1);

        while ($resultat1 = $ReqLog1->fetch_array()) {
            $current_gid=$resultat1["g_gid"];
            if ($list_groupes_double_valid_du_resp=="")
                $list_groupes_double_valid_du_resp="$current_gid";
            else
                $list_groupes_double_valid_du_resp=$list_groupes_double_valid_du_resp.", $current_gid";
        }
    }
    return $list_groupes_double_valid_du_resp;

}

// recup de la liste des groupes à double validation, dont $resp_login est GRAND responsable
// renvoit une liste de gid séparés par des virgules
function get_list_groupes_double_valid_du_grand_resp($resp_login)
{

    $list_groupes_double_valid_du_grand_resp="";
    $db = \includes\SQL::singleton();

    $sql1='SELECT DISTINCT(ggr_gid) FROM conges_groupe_grd_resp WHERE ggr_login="'. $db->quote($resp_login).'" ORDER BY ggr_gid ';
    $ReqLog1 = $db->query($sql1);

    while ($resultat1 = $ReqLog1->fetch_array())
    {
        $current_gid=$resultat1["ggr_gid"];
        if ($list_groupes_double_valid_du_grand_resp=="")
            $list_groupes_double_valid_du_grand_resp="$current_gid";
        else
            $list_groupes_double_valid_du_grand_resp=$list_groupes_double_valid_du_grand_resp.", $current_gid";
    }
    return $list_groupes_double_valid_du_grand_resp;
}

// recup de la liste des groupes dont $resp_login est membre
// renvoit une liste de group_id séparés par des virgules
function get_list_groupes_du_user($user_login)
{
    $list_group=array();
    $db = \includes\SQL::singleton();
    $sql1='SELECT gu_gid FROM conges_groupe_users WHERE gu_login="'. $db->quote($user_login).'" ORDER BY gu_gid';
    $ReqLog1 = $db->query($sql1);

    while ($resultat1 = $ReqLog1->fetch_array())
        $list_group[] = $resultat1["gu_gid"];
    $list_group = implode(' , ', $list_group);
    return $list_group;
}

// recup de la liste de TOUS les users (sauf "conges" et "admin"
// renvoit une liste de login entre quotes et séparés par des virgules
function get_list_all_users()
{
    $list_users="";
    $sql1="SELECT DISTINCT(u_login) FROM conges_users ORDER BY u_login " ;
    $ReqLog1 = \includes\SQL::singleton()->query($sql1);

    while ($resultat1 = $ReqLog1->fetch_array())
    {
        $current_login=$resultat1["u_login"];
        if ($list_users=="")
            $list_users="'$current_login'";
        else
            $list_users=$list_users.", '$current_login'";
    }
    return $list_users;
}

// construit le tableau des responsables d'un user
// le login du user est passé en paramêtre ainsi que le tableau (vide) des resp
//renvoit un tableau indexé de resp_login => "absent" ou "present"
function get_tab_resp_du_user($user_login)
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);
    $tab_resp=array();
    // recup des resp des groupes du user
    $list_groups=get_list_groupes_du_user($user_login);
    if ($list_groups!="") {
        $tab_gid=explode(",", $list_groups);
        foreach ($tab_gid as $gid) {
            $gid=trim($gid);
            $sql2='SELECT gr_login FROM conges_groupe_resp WHERE gr_gid=' . $db->quote($gid) . ' AND gr_login!=\'' . $db->quote($user_login) . '\'';
            $ReqLog1 = $db->query($sql2);

            while ($resultat1 = $ReqLog1->fetch_array()) {
                //attention à ne pas mettre 2 fois le meme resp dans le tableau
                if (in_array($resultat1["gr_login"], $tab_resp)==FALSE)
                    $tab_resp[$resultat1["gr_login"]]="present";
            }
        }
    }

    /***************************/
    // Gestion des responsable inactifs
    // Si ils sont inactifs on les gère comme des responsables absents
    $nb_present=count($tab_resp);
    foreach ($tab_resp as $current_resp => $presence )
    {
        // verif dans la base si le current_resp est absent :
        $req = 'SELECT u_is_active FROM conges_users WHERE u_login=\''. $db->quote($current_resp).'\';';
        $ReqLog_2 = $db->query($req);
        $rec = $ReqLog_2->fetch_array();
        if ($rec['u_is_active'] == 'N') {
            $nb_present=$nb_present-1;
            $tab_resp[$current_resp]="absent";
        }
    }
    if ($nb_present==0) {
        $new_tab_resp=array();
        foreach ($tab_resp as $current_resp => $presence)
        {
            // attention ,on evite le cas ou le user est son propre resp (sinon on boucle infiniment)
            if ($current_resp != $user_login)
                $new_tab_resp = array_merge  ( $new_tab_resp , get_tab_resp_du_user($current_resp));
        }
        $tab_resp = array_merge  ( $tab_resp, $new_tab_resp);
    }

    /************************************/
    // gestion des absence des responsables :
    // on verifie que les resp sont présents, si tous absent, on cherhe les resp des resp, et ainsi de suite ....
    if ($config->isGestionResponsableAbsent()) {
        // on va verifier si les resp récupérés sont absents
        foreach ($tab_resp as $current_resp => $presence ) {
            // verif dans la base si le current_resp est absent :
            $req = 'SELECT p_num
                     FROM conges_periode
                     WHERE p_login =\''. $db->quote($current_resp).'\'
                     AND p_etat = \'ok\'
                     AND TO_DAYS(conges_periode.p_date_deb) <= TO_DAYS(NOW())
                     AND TO_DAYS(conges_periode.p_date_fin) >= TO_DAYS(NOW())';
            $ReqLog_3 = $db->query($req);
            if ($ReqLog_3->num_rows!=0) {
                $nb_present=$nb_present-1;
                $tab_resp[$current_resp]="absent";
            }
        }

        //si aucun resp present on recupere les resp du resp
        if ($nb_present==0) {
            $new_tab_resp=array();
            foreach ($tab_resp as $current_resp => $presence) {
                // attention ,on evite le cas ou le user est son propre resp (sinon on boucle infiniment)
                if ($current_resp != $user_login)
                    $new_tab_resp = array_merge  ( $new_tab_resp , get_tab_resp_du_user($current_resp));
            }
            $tab_resp = array_merge  ( $tab_resp, $new_tab_resp);
        }
    }
    // FIN gestion des absence des responsables :
    /************************************/

    return $tab_resp;
}


// construit le tableau des grands responsables d'un user
// (tab des grd resp des groupes à double_valid dont le user fait partie
// le login du user est passé en paramêtre ainsi que le tableau (vide) des resp
function get_tab_grd_resp_du_user($user_login, &$tab_grd_resp)
{
    $db = \includes\SQL::singleton();
    // recup des resp des groupes du user
    $list_groups=get_list_groupes_du_user($user_login);
    if ($list_groups!="") {
        $tab_gid=explode(",", $list_groups);
        foreach($tab_gid as $gid) {
            $gid=trim($gid);
            $sql1='SELECT ggr_login FROM conges_groupe_grd_resp WHERE ggr_gid='. $db->quote($gid);
            $ReqLog1 = $db->query($sql1);

            while ($resultat1 = $ReqLog1->fetch_array()) {
                //attention à ne pas mettre 2 fois le meme resp dans le tableau
                if (in_array($resultat1["ggr_login"], $tab_grd_resp)==FALSE) {
                    $tab_grd_resp[]=$resultat1["ggr_login"];
                }
            }
        }
    }
}



/**
 * verifie si un user est responsable ou pas
 *
 * @param string $login Paramètre inutile, mais à but de compat
 */
function is_resp($login)
{
    return isset($_SESSION['is_resp']) && 'Y' === $_SESSION['is_resp'];
}

/**
 * verifie si un user est HR ou pas
 *
 * @param string $login Paramètre inutile, mais à but de compat
 */
function is_hr($login)
{
    return isset($_SESSION['is_hr']) && 'Y' === $_SESSION['is_hr'];
}

// verifie si un user est valide ou pas
// renvoit TRUE si le login est enable dans la table conges_users, FALSE sinon.
function is_active($login)
{
    static $sql_is_active = array();
    $db = \includes\SQL::singleton();
    if (!isset($sql_is_active[$login])) {
        // recup de qq infos sur le user
        $select_info='SELECT u_is_active FROM conges_users WHERE u_login="'. $db->quote($login).'";';
        $ReqLog_info = $db->query($select_info);
        $resultat_info = $ReqLog_info->fetch_array();
        $sql_is_active[$login]=$resultat_info["u_is_active"];
    }

    return ($sql_is_active[$login]=='Y');
}


/**
 * verifie si un user est admin ou pas
 *
 * @param string $login Paramètre inutile, mais à but de compat
 */
function is_admin($login)
{
    return isset($_SESSION['is_admin']) && 'Y' === $_SESSION['is_admin'];
}

// on insert une nouvelle periode dans la table periode
// retourne le num d'auto_incremente (p_num) ou 0 en cas l'erreur
function insert_dans_periode($login, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin, $nb_jours, $commentaire, $id_type_abs, $etat, $id_fermeture)
{
    // Récupération du + grand p_num (+ grand numero identifiant de conges)
    $db = \includes\SQL::singleton();
    $sql1 = "SELECT max(p_num) FROM conges_periode" ;
    $ReqLog1 = $db->query($sql1);
    if ( $num_new_demande = $ReqLog1->fetch_row() )
        $num_new_demande = $num_new_demande[0] +1;
    else
        $num_new_demande = 1;

    $sql2 = "INSERT INTO conges_periode SET p_login='$login',p_date_deb='$date_deb', p_demi_jour_deb='$demi_jour_deb',p_date_fin='$date_fin', p_demi_jour_fin='$demi_jour_fin', p_nb_jours='$nb_jours', p_commentaire='". $db->quote($commentaire)."', p_type='$id_type_abs', p_etat='$etat', ";

    if ($id_fermeture!=0)
        $sql2 = $sql2." p_fermeture_id='$id_fermeture' ," ;
    if ($etat=="demande")
        $sql2 = $sql2." p_date_demande=NOW() ," ;
    else
        $sql2 = $sql2." p_date_traitement=NOW() ," ;

    $sql2 = $sql2." p_num='$num_new_demande' " ;
    $result = $db->query($sql2);

    if ($id_fermeture!=0)
        $comment_log = "saisie de fermeture num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";
    elseif ($etat=="demande")
        $comment_log = "demande de conges num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";
    else
        $comment_log = "saisie de conges num $num_new_demande (type $id_type_abs) pour $login ($nb_jours jours) (de $date_deb $demi_jour_deb à $date_fin $demi_jour_fin)";

    log_action($num_new_demande, $etat, $login, $comment_log);

    if ($result)
        return $num_new_demande;
    else
        return 0;
}


// remplit le tableau global des jours feries a partir de la database
function init_tab_jours_feries()
{
    if (empty($_SESSION['tab_j_feries']))
    {
        $_SESSION['tab_j_feries'] = [];

        $sql_select='SELECT jf_date FROM conges_jours_feries;';
        $res_select = \includes\SQL::singleton()->query($sql_select);

        while ($row = $res_select->fetch_array())
        {
            $_SESSION['tab_j_feries'][]=$row['jf_date'];
        }
    }
}

// renvoit TRUE si le jour est chomé (férié), sinon FALSE (verifie dans le tableau global $_SESSION["tab_j_feries"]
function est_chome($timestamp)
{
    $j_date=date("Y-m-d", $timestamp);
    if (isset($_SESSION["tab_j_feries"]))
        return in_array($j_date, $_SESSION["tab_j_feries"]);
    else
        return FALSE;
}

// initialise le tableau des variables de config (renvoit un tableau)
function init_config_tab()
{
    static $userlogin = null;
    static $result = null;
    $db = \includes\SQL::singleton();
    if ($result === null || (isset($_SESSION['userlogin']) && $userlogin != $_SESSION['userlogin'])) {

        include ROOT_PATH .'version.php';
        include_once CONFIG_PATH .'dbconnect.php';
        $tab = [];


        /******************************************/
        //  recup des variables de version.php
        if (isset($config_php_conges_version)) $tab['php_conges_version'] = $config_php_conges_version ;
        if (isset($config_url_site_web_php_conges)) $tab['url_site_web_php_conges'] = $config_url_site_web_php_conges ;

        /******************************************/
        //  recup des variables de la table conges_appli
        $sql_appli = "SELECT appli_variable, appli_valeur FROM conges_appli;";
        $req_appli = $db->query($sql_appli) ;

        while ($data_appli = $req_appli->fetch_array())
        {
            $key    = $data_appli[0];
            $value    = $data_appli[1];
            $tab[$key]    = $value;
        }

        /******************************************/
        //  recup des mails dans  la table conges_mail
        $sql_mail = "SELECT mail_nom, mail_subject, mail_body FROM conges_mail;";
        $req_mail = $db->query($sql_mail) ;

        while ($data_mail = $req_mail->fetch_array())
        {
            $mail_nom    = $data_mail[0];
            $key1    = $mail_nom."_sujet";
            $key2    = $mail_nom."_contenu";
            $sujet    = $data_mail[1];
            $corps    = $data_mail[2];
            $tab[$key1] = $sujet ;
            $tab[$key2] = $corps ;
        }

        /******************************************/
        //  config_ldap.php
        if (file_exists(CONFIG_PATH .'config_ldap.php')) {
            include CONFIG_PATH .'config_ldap.php';
            if (isset($config_ldap_protocol_version))
                $tab['ldap_protocol_version'] = $config_ldap_protocol_version ;
            else
                $tab['ldap_protocol_version'] = 0;
            if(isset($config_ldap_server))    $tab['ldap_server']    = $config_ldap_server ;
            if(isset($config_ldap_bupsvr))    $tab['ldap_bupsvr']    = $config_ldap_bupsvr ;
            if(isset($config_basedn))    $tab['basedn']        = $config_basedn ;
            if(isset($config_ldap_user))    $tab['ldap_user']    = $config_ldap_user ;
            if(isset($config_ldap_pass))    $tab['ldap_pass']    = $config_ldap_pass ;
            if(isset($config_searchdn))    $tab['searchdn']    = $config_searchdn ;
            if(isset($config_ldap_prenom))    $tab['ldap_prenom']    = $config_ldap_prenom ;
            if(isset($config_ldap_nom))    $tab['ldap_nom']    = $config_ldap_nom ;
            if(isset($config_ldap_mail))    $tab['ldap_mail']    = $config_ldap_mail ;
            if(isset($config_ldap_login))    $tab['ldap_login']    = $config_ldap_login ;
            if(isset($config_ldap_nomaff))    $tab['ldap_nomaff']    = $config_ldap_nomaff ;
            if(isset($config_ldap_filtre))    $tab['ldap_filtre']    = $config_ldap_filtre ;
            if(isset($config_ldap_filrech))    $tab['ldap_filrech']    = $config_ldap_filrech ;
        }

        /******************************************/
        //  config_CAS.php
        if (file_exists(CONFIG_PATH .'config_CAS.php')) {
            include CONFIG_PATH .'config_CAS.php';
            if (isset($config_CAS_host))    $tab['CAS_host']    = $config_CAS_host ;
            if (isset($config_CAS_portNumber)) $tab['CAS_portNumber'] = $config_CAS_portNumber ;
            if (isset($config_CAS_URI))    $tab['CAS_URI']        = $config_CAS_URI ;
            if (isset($config_CAS_CACERT))    $tab['CAS_CACERT']        = $config_CAS_CACERT ;
        }

        /******************************************/
        //  recup de qq infos sur le user
        if (isset($_SESSION['userlogin']))
        {
            $sql_user = "SELECT u_nom, u_prenom, u_is_resp, u_is_admin, u_is_hr, u_is_active FROM conges_users WHERE u_login='".$_SESSION['userlogin']."' ";
            $req_user = $db->query($sql_user) ;

            if ($data_user = $req_user->fetch_array()) {
                $_SESSION['u_nom']    = $data_user[0] ;
                $_SESSION['u_prenom']    = $data_user[1] ;
                $_SESSION['is_resp']    = $data_user[2] ;
                $_SESSION['is_admin']    = $data_user[3] ;
                $_SESSION['is_hr']    = $data_user[4] ;
                $_SESSION['is_active']    = $data_user[5] ;
            }
        }

        /******************************************/
        $result = $tab;
        if (isset($_SESSION['userlogin']))
            $userlogin = $_SESSION['userlogin'];
    }
    return $result;
}

// Récupère le contenu d une variable $_GET / $_POST
function getpost_variable($variable, $default="")
{
   $valeur = (isset($_POST[$variable]) ? $_POST[$variable]  : (isset($_GET[$variable]) ? $_GET[$variable]   : $default));

   return   $valeur;
}

// recup dans un tableau des types de conges
function recup_tableau_types_conges()
{
    $result = array();
    $request = 'SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type=\'conges\';';
    $data   = \includes\SQL::singleton()->query($request);

    while ($l = $data->fetch_array()) {
        $id = $l['ta_id'];
        $result[$id] = $l['ta_libelle'];
    }
    return $result;
}

// recup dans un tableau des types d'absence
function recup_tableau_types_absence()
{
    $result = array();
    $request = 'SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type=\'absences\';';
    $data   = \includes\SQL::singleton()->query($request);

    while ($l = $data->fetch_array())
    {
        $id = $l['ta_id'];
        $result[$id] = $l['ta_libelle'];
    }
    return $result;
}

// recup dans un tableau des types de conges exceptionnels
function recup_tableau_types_conges_exceptionnels()
{
    $result = array();
    $request = 'SELECT ta_id, ta_libelle FROM conges_type_absence WHERE ta_type=\'conges_exceptionnels\';';
    $data   = \includes\SQL::singleton()->query($request);

    while ($l = $data->fetch_array())
    {
        $id = $l['ta_id'];
        $result[$id] = $l['ta_libelle'];
    }
    return $result;
}

// recup dans un tableau de tableau les infos des types de conges et absences
function recup_tableau_tout_types_abs( )
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);
    $result = array();
    if ($config->isCongesExceptionnelsActive()) // on prend tout les types de conges
        $request = 'SELECT ta_id, ta_type, ta_libelle, ta_short_libelle FROM conges_type_absence;';
    else // on prend tout les types de conges SAUF les conges exceptionnels
        $request = 'SELECT ta_id, ta_type, ta_libelle, ta_short_libelle FROM conges_type_absence WHERE conges_type_absence.ta_type != \'conges_exceptionnels\';';

    $data = $db->query($request);

    while ($resultat_cong = $data->fetch_array())
    {
        $id = $resultat_cong['ta_id'];
        $result[$id] = array('type' =>  $resultat_cong['ta_type'],'libelle' => $resultat_cong['ta_libelle'],'short_libelle' =>  $resultat_cong['ta_short_libelle'],);
    }
    return $result;
}

// recup dans un tableau de tableaux les nb et soldes de conges d'un user (indicé par id de conges)
function recup_tableau_conges_for_user($login, $hide_conges_exceptionnels)
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);
    // on pourrait tout faire en un seule select, mais cela bug si on change la prise en charge des conges exceptionnels en cours d'utilisation ...

    if ($config->isCongesExceptionnelsActive() && ! $hide_conges_exceptionnels) // on prend tout les types de conges
        $request = 'SELECT ta_libelle, su_nb_an, su_solde, su_reliquat FROM conges_solde_user, conges_type_absence WHERE conges_type_absence.ta_id = conges_solde_user.su_abs_id AND su_login = "'. $db->quote($login).'" ORDER BY su_abs_id ASC;';
    else // on prend tout les types de conges SAUF les conges exceptionnels
        $request = 'SELECT ta_libelle, su_nb_an, su_solde, su_reliquat FROM conges_solde_user, conges_type_absence WHERE conges_type_absence.ta_type != \'conges_exceptionnels\' AND conges_type_absence.ta_id = conges_solde_user.su_abs_id AND su_login = "'. $db->quote($login).'" ORDER BY su_abs_id ASC;';

    $data   = $db->query($request);

    $result = array();

    while ($l = $data->fetch_array())
    {
        $sql_id = $l['ta_libelle'];
        $result[$sql_id] = array('nb_an' => affiche_decimal($l['su_nb_an']),'solde' => affiche_decimal($l['su_solde']),'reliquat' => affiche_decimal($l['su_reliquat']),);
    }

    return $result;
}

// affichage du tableau récapitulatif des solde de congés d'un user
function affiche_tableau_bilan_conges_user($login)
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);
    $request = 'SELECT u_quotite FROM conges_users where u_login = "'. $db->quote($login).'";';
    $ReqLog = $db->query($request) ;
    $resultat = $ReqLog->fetch_array();
    $sql_quotite=$resultat['u_quotite'];
    $return = '';

    // recup dans un tableau de tableaux les nb et soldes de conges d'un user
    $tab_cong_user = recup_tableau_conges_for_user($login, true);

    // recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
    if ($config->isCongesExceptionnelsActive()) {
        $tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();
    }

    $return .= '<table class="table table-hover table-responsive table-condensed table-bordered">';
    $return .= '<thead>';
    $colspan = count($tab_cong_user) * 2 + 1 ;
    $colspan = $config->isHeuresAutorise() ? $colspan + 1 : $colspan;
    $return .= '<tr><td></td><td colspan="' . $colspan . '">SOLDES</td></tr>';
    $return .= '<tr>';
    $return .= '<th class="titre">'. _('divers_quotite') .'</th>';

    foreach($tab_cong_user as $id => $val) {
        if ($config->isCongesExceptionnelsActive() && in_array($id,$tab_type_conges_exceptionnels)) {
            $return .= '<th class="solde">' . $id . '</th>';
        } else {
            $return .= '<th class="annuel">' . $id . ' / ' . _('divers_an_maj') . '</th><th class="solde">' . $id . '</th>';
        }
    }
    if ($config->isHeuresAutorise()) {
        $return .= '<th class="solde">' . _('heure') . '</th>';
    }
    $return .= '</tr>';
    $return .= '</thead>';
    $return .= '<tbody>';
    $return .= '<tr>';
    $return .= '<td class="quotite">' . $sql_quotite . '%</td>';
    foreach($tab_cong_user as $id => $val) {
        if ($config->isCongesExceptionnelsActive()  && in_array($id,$tab_type_conges_exceptionnels)) {
            $return .= '<td class="solde">' . $val['solde'] . ($val['reliquat'] > 0 ? ' (' . _('dont_reliquat') . ' ' . $val['reliquat'] . ')' : '') . '</td>';
        } else {
            $return .= '<td class="annuel">' . $val['nb_an'] . '</td><td class="solde">' . $val['solde'] . ($val['reliquat'] > 0 ? ' (' . _('dont_reliquat') . ' ' . $val['reliquat'] . ')' : '') . '</td>';
        }
    }
    if ($config->isHeuresAutorise()) {
        $timestampSolde = \App\ProtoControllers\Utilisateur::getSoldeHeure($login);
        $return .= '<td class="solde">'. \App\Helpers\Formatter::timestamp2Duree($timestampSolde) .'</td>';
    }
    $return .= '</tr>';
    $return .= '</tbody>';
    $return .= '</table>';
    return $return;
}

// renvoit un tableau de tableau contenant les informations du user
// renvoit FALSE si erreur
function recup_infos_du_user($login, $list_groups_double_valid)
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);
    $tab=array();
    $sql1 = 'SELECT * FROM conges_users ' .
            'WHERE u_login="'. $db->quote($login).'";';
    $ReqLog = $db->query($sql1) ;

    if ($resultat = $ReqLog->fetch_array()) {
        $tab_user=array();
        $tab_user['login']    = $resultat['u_login'];;
        $tab_user['nom']    = $resultat['u_nom'];
        $tab_user['prenom']    = $resultat['u_prenom'];
        $tab_user['is_resp']    = $resultat['u_is_resp'];
        $tab_user['is_admin']    = $resultat['u_is_admin'];
        $tab_user['is_hr']    = $resultat['u_is_hr'];
        $tab_user['is_active']    = $resultat['u_is_active'];
        $tab_user['passwd']    = $resultat['u_passwd'];
        $tab_user['quotite']    = $resultat['u_quotite'];
        $tab_user['solde_heure']    = $resultat['u_heure_solde'];
        $tab_user['email']    = $resultat['u_email'];
        $tab_user['num_exercice'] = $resultat['u_num_exercice'];
        $tab_user['planningId']   = $resultat['planning_id'];
        $tab_user['conges']    = recup_tableau_conges_for_user($login, false);

        $tab_user['double_valid'] = "N";

        // on regarde ici si le user est dans un groupe qui fait l'objet d'une double validation
        if ($config->isDoubleValidationActive()) {
            if ($list_groups_double_valid!="") { // si $resp_login est responsable d'au moins un groupe a double validation
                $sql1='SELECT gu_login FROM conges_groupe_users WHERE gu_login="'. $db->quote($login).'" AND gu_gid IN ('.$list_groups_double_valid.') ORDER BY gu_gid, gu_login;';
                $ReqLog1 = $db->query($sql1);

                if ($ReqLog1->num_rows  !=0)
                    $tab_user['double_valid'] = 'Y';
            }
        }
        return $tab_user ;
    }
    else
        return FALSE;
}

// renvoit un tableau de tableau contenant les informations de tous les users
function recup_infos_all_users()
{
    $tab=array();
    $list_groupes_double_validation=get_list_groupes_double_valid();
    $sql1 = "SELECT u_login FROM conges_users ORDER BY u_nom";
    $ReqLog = \includes\SQL::singleton()->query($sql1);

    while ($resultat =$ReqLog->fetch_array())
    {
        $tab_user=array();
        $sql_login=$resultat["u_login"];

        $tab[$sql_login] = recup_infos_du_user($sql_login, $list_groupes_double_validation);
    }
    return $tab ;
}

// renvoit un tableau de tableau contenant les informations de tous les users d'un groupe donné
function recup_infos_all_users_du_groupe($group_id)
{
    $tab=array();
    // recup de la liste de tous les users du groupe ...
    $list_all_users_du_groupe = get_list_users_du_groupe($group_id);
    $list_groupes_double_validation=get_list_groupes_double_valid();
    if (strlen($list_all_users_du_groupe)!=0)
    {
        $tab_users_du_groupe=explode(",", $list_all_users_du_groupe);
        foreach($tab_users_du_groupe as $current_login)
        {
            $current_login = trim($current_login);
            $current_login = trim($current_login, "\'");  // on enleve les quotes qui ont été ajouté lors de la creation de la liste
            $tab[$current_login] = recup_infos_du_user($current_login, $list_groupes_double_validation);
        }
    }
    return $tab ;
}

// renvoit un tableau de tableau contenant les informations de tous les users dont $login est responsable
function recup_infos_all_users_du_resp($login)
{
    $tab=array();

        $groupeIds = \App\ProtoControllers\Responsable::getIdGroupeResp($login);
        $listUsers = \App\ProtoControllers\Groupe\Utilisateur::getListUtilisateurByGroupeIds($groupeIds);

    // recup de la liste des groupes à double validation, dont $login est responsable
    // (servira à dire pour chaque user s'il est dans un de ces groupe ou non , donc s'il fait l'objet d'une double valid ou non )
    $list_groups_double_valid_du_resp=get_list_groupes_double_valid_du_resp($login);

    if (!empty($listUsers)) {
        foreach($listUsers as $current_login) {
            $current_login = trim($current_login);
            $current_login = trim($current_login, "\'");  // on enleve les quotes qui ont été ajouté lors de la creation de la liste

            $tab[$current_login] = recup_infos_du_user($current_login, $list_groups_double_valid_du_resp);
        }
    }

    return $tab ;
}

// renvoit un tableau de tableau contenant les informations de tous les users dont $login est GRAND responsable
function recup_infos_all_users_du_grand_resp($login)
{
    $db = \includes\SQL::singleton();
    $tab=array();
    $list_groups_double_valid=get_list_groupes_double_valid_du_grand_resp($login);

    if ($list_groups_double_valid!="")
    {
        // recup de la liste des users des groupes de la liste $list_groups_double_valid
        $sql_users = 'SELECT DISTINCT(gu_login) FROM conges_groupe_users, conges_users WHERE gu_gid IN ('. $db->quote($list_groups_double_valid).') AND gu_login=u_login ORDER BY u_nom;';
        $ReqLog_users = $db->query($sql_users) ;
        $list_all_users_dbl_valid="";
        while ($resultat_users =$ReqLog_users->fetch_array())
        {
            $current_login=$resultat_users["gu_login"];
            if ($list_all_users_dbl_valid=="")
                $list_all_users_dbl_valid="'$current_login'";
            else
                $list_all_users_dbl_valid=$list_all_users_dbl_valid.", '$current_login'";
        }

        if ($list_all_users_dbl_valid!="")
        {
            $tab_users_du_resp=explode(",", $list_all_users_dbl_valid);
            foreach($tab_users_du_resp as $current_login)
            {
                $current_login = trim($current_login);
                $current_login = trim($current_login, "\'");  // on enleve les qote qui on été ajouté lors de la creation de la liste
                $tab[$current_login] = recup_infos_du_user($current_login, $list_groups_double_valid);
            }
        }
    }
    return $tab ;
}

// verif des droits du user à afficher la page qu'il demande (pour éviter les hacks par bricolage d'URL)

function verif_droits_user($niveau_droits)
{
    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
    $niveau_droits = strtolower($niveau_droits);

    // verif si $_SESSION['is_admin'] ou $_SESSION['is_resp'] ou $_SESSION['is_hr'] =="N" ou $_SESSION['is_active'] =="N"
    if ($_SESSION[$niveau_droits]=="N") {
        // on recupere les variable utiles pour le suite :
        $url_accueil_conges = $config->getUrlAccueil();
        $lang_divers_acces_page_interdit =  _('divers_acces_page_interdit');
        $lang_divers_user_disconnected    =  _('divers_user_disconnected');
        $lang_divers_veuillez        =  _('divers_veuillez');
        $lang_divers_vous_authentifier    =  _('divers_vous_authentifier');

        // on delete la session et on renvoit sur l'authentification (page d'accueil)
        session_delete();

        // message d'erreur !
        echo "<center>\n";
        echo "<font color=\"red\">$lang_divers_acces_page_interdit</font><br>$lang_divers_user_disconnected<br>\n";
        echo "$lang_divers_veuillez <a href='$url_accueil_conges/authentification' target='_top'> $lang_divers_vous_authentifier .</a>\n";
        echo "</center>\n";
        exit;
    }
}

// on insert les logs des periodes de conges
// retourne TRUE ou FALSE
function log_action($num_periode, $etat_periode, $login_pour, $comment)
{
    $db = \includes\SQL::singleton();
    $comment = htmlentities($comment, ENT_QUOTES | ENT_HTML401);

    if (isset($_SESSION['userlogin']))
        $user = $_SESSION['userlogin'] ;
    else
        $user = "inconnu";

    $sql1 = 'INSERT INTO conges_logs SET log_p_num="'. $db->quote($num_periode).'",log_user_login_par="'. $db->quote($user).'",log_user_login_pour="'. $db->quote($login_pour).'",log_etat="'. $db->quote($etat_periode).'",log_comment="'. $db->quote($comment).'",log_date=NOW()';
    $result = $db->query($sql1);

    return $result;
}

// remplit le tableau global des jours feries a partir de la database
function init_tab_jours_fermeture($user)
{
    $db = \includes\SQL::singleton();
    $_SESSION["tab_j_fermeture"]=array();
    $sql_select='SELECT DISTINCT jf_date FROM conges_jours_fermeture, conges_groupe_users WHERE gu_login="'. $db->quote($user).'" AND gu_gid=jf_gid';
    $res_select = $db->query($sql_select);

    while( $row = $res_select->fetch_array())
        $_SESSION["tab_j_fermeture"][]=$row["jf_date"];
}

// renvoit TRUE si le jour est fermé (fermeture), sinon FALSE (verifie dans le tableau global $_SESSION["tab_j_fermeture"]
function est_ferme($timestamp)
{
    $j_date=date("Y-m-d", $timestamp);
    if (isset($_SESSION["tab_j_fermeture"]))
        return in_array($j_date, $_SESSION["tab_j_fermeture"]);
    else
        return FALSE;
}

// renvoit le "su_reliquat" pour un user et un type de conges donné
function get_reliquat_user_conges($login, $type_abs)
{
    $db = \includes\SQL::singleton();
    $select_info='SELECT su_reliquat FROM conges_solde_user WHERE su_login="'. $db->quote($login).'" AND su_abs_id="'. $db->quote($type_abs).'"';
    $ReqLog_info = $db->query($select_info);
    $resultat_info = $ReqLog_info->fetch_array();
    $sql_reliquat=$resultat_info["su_reliquat"];

    return $sql_reliquat;
}

/*  si date_fin_conges < date_limite_reliquat => alors on décompte dans reliquats
    si date_debut_conges > date_limite_reliquat => alors on ne décompte pas dans reliquats
    si gonges demandé est à cheval sur la date_limite_reliquat => il faut decompter le nb_jours_pris du solde, puis il faut
    calculer le nb_jours_avant pris avant la date limite, et on le decompte des reliquats, et calculer le nb_jours_apres
    d'apres la date limite et ne pas le décompter des reliquats !!!
*/
function soustrait_solde_et_reliquat_user($user_login, $num_current_periode, $user_nb_jours_pris, $type_abs, $date_deb, $demi_jour_deb, $date_fin, $demi_jour_fin)
{
    $db = \includes\SQL::singleton();
    $config = new \App\Libraries\Configuration($db);
    $new_reliquat = null;

    $VerifDec = verif_saisie_decimal($user_nb_jours_pris);

    //si on autorise les reliquats
    if ($config->isReliquatsAutorise()) {
        //recup du reliquat du user pour ce type d'absence
        $reliquat=get_reliquat_user_conges($user_login, $type_abs);
        //echo "reliquat = $reliquat<br>\n";
        // s'il y a une date limite d'utilisationdes reliquats (au format jj-mm)
        if ($config->getDateLimiteReliquats() != 0) {
            //si date_fin_conges < date_limite_reliquat => alors on décompte dans reliquats
            if ($date_fin < $_SESSION['config']['date_limite_reliquats']) {
                if ($reliquat>$user_nb_jours_pris)
                    $new_reliquat = $reliquat-$user_nb_jours_pris;
                else
                    $new_reliquat = 0;
            }
            //si date_debut_conges > date_limite_reliquat => alors on ne décompte pas dans reliquats
            elseif ($date_deb >= $_SESSION['config']['date_limite_reliquats']) {
                $new_reliquat = $reliquat;
            } else {
            //si conges demandé est à cheval sur la date_limite_reliquat => il faut decompter le nb_jours_pris du solde, puis il faut
            //calculer le nb_jours_avant pris avant la date limite, et on le decompte des reliquats, et calculer le nb_jours_apres
            //d'apres la data limite et ne pas le décompter des reliquats !!!
                $comment="calcul reliquat -> date limite" ;
                $nb_reliquats_a_deduire = compter($user_login, $num_current_periode, $date_deb, $_SESSION['config']['date_limite_reliquats'], $demi_jour_deb, "pm", $comment );

                if ($reliquat > $nb_reliquats_a_deduire)
                    $new_reliquat = $reliquat - $nb_reliquats_a_deduire;
                else
                    $new_reliquat = 0;
            }
        } else {
        // s'il n'y a pas de date limite d'utilisation des reliquats
            if ($reliquat>$user_nb_jours_pris)
                $new_reliquat = $reliquat-$user_nb_jours_pris;
            else
                $new_reliquat = 0;
        }
        $VerifDec = verif_saisie_decimal($user_nb_jours_pris);
        $VerifDec = verif_saisie_decimal($new_reliquat);
        $sql2 = 'UPDATE conges_solde_user SET su_solde=su_solde-'. $db->quote($user_nb_jours_pris).', su_reliquat='. $db->quote($new_reliquat).' WHERE su_login="'. $db->quote($user_login).'"  AND su_abs_id='. $db->quote($type_abs).' ';
    } else {
        $VerifDec = verif_saisie_decimal($user_nb_jours_pris);
        $VerifDec = verif_saisie_decimal($new_reliquat);
        $sql2 = 'UPDATE conges_solde_user SET su_solde=su_solde-'. $db->quote($user_nb_jours_pris).' WHERE su_login=\''. $db->quote($user_login).'\'  AND su_abs_id=\''.$type_abs.'\' ';
    }
    $ReqLog2 = $db->query($sql2) ;
}


/*--------- ajout fonction probesys -------------------*/
//date au format d/m/Y -> Y-m-d
function convert_date($date)
{
    $date_component = explode('/', $date);
    $date_component = array_reverse($date_component);

    return implode('-', $date_component);
}

//date au format d/m/Y -> d-m-Y
function revert_date($date)
{
    $date_component = explode('-', $date);

    return implode('/', $date_component);
}
