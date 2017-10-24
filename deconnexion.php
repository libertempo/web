<?php

define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';

include_once ROOT_PATH .'fonctions_conges.php';
include_once INCLUDE_PATH .'fonction.php';
include_once INCLUDE_PATH .'session.php';

$how_to_connect_user=$_SESSION['config']['how_to_connect_user'];
$URL_ACCUEIL_CONGES=$_SESSION['config']['URL_ACCUEIL_CONGES'];

$comment_log = "Deconnexion de ".$_SESSION['userlogin'];
log_action(0, "", $_SESSION['userlogin'], $comment_log);

//Dans le cas ou le système d'authentification CAS est utilisé, lorsque l'utilisateur se deconnecte,
// on détruit le ticket qui a permis d'authentifier l'utilisateur.
if($how_to_connect_user=="cas")
{
    $logoutCas=1;
    deconnexion_CAS($URL_ACCUEIL_CONGES);
}

session_delete();

$session="";
$session_username="";
$session_password="";

redirect( $URL_ACCUEIL_CONGES );
