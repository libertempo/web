<?php

define('ROOT_PATH', '');
require ROOT_PATH . 'define.php';

$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include ROOT_PATH .'fonctions_conges.php';
include INCLUDE_PATH .'fonction.php';
include INCLUDE_PATH .'session.php';

$DEBUG=FALSE;
	
$how_to_connect_user=$_SESSION['config']['how_to_connect_user'];
$URL_ACCUEIL_CONGES=$_SESSION['config']['URL_ACCUEIL_CONGES'];

$comment_log = "Deconnexion de ".$_SESSION['userlogin'];
log_action(0, "", $_SESSION['userlogin'], $comment_log, $DEBUG);

//Dans le cas ou le système d'authentification CAS est utilisé, lorsque l'utilisateur se deconnecte,
// on détruit le ticket qui a permis d'authentifier l'utilisateur.
if($how_to_connect_user=="cas")
{
    $logoutCas=1;
    deconnexion_CAS($URL_ACCUEIL_CONGES);
}

session_delete($session);

$session="";
$session_username="";
$session_password="";

redirect( $URL_ACCUEIL_CONGES );
