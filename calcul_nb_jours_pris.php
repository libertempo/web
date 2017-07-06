<?php

define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';


include_once ROOT_PATH . 'fonctions_conges.php' ;
include_once INCLUDE_PATH . 'fonction.php';
include_once INCLUDE_PATH . 'session.php';
include_once ROOT_PATH . 'fonctions_calcul.php';

/*** initialisation des variables ***/
$user="";
$date_debut="";
$date_fin="";
$p_num="";

/*************************************/
// recup des parametres reçus :
// SERVER
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
// GET  / POST
$user       = getpost_variable('user') ;
$date_debut = getpost_variable('date_debut') ;
$date_fin   = getpost_variable('date_fin') ;
$opt_debut  = getpost_variable('opt_debut') ;
$opt_fin    = getpost_variable('opt_fin') ;
$p_num	    = getpost_variable('p_num') ;
/*************************************/

if( ($user!="") && ($date_debut!="") && ($date_fin!="") && ($opt_debut!="") && ($opt_fin!="") )
	affichage($user, $date_debut, $date_fin, $opt_debut, $opt_fin, $p_num);

/**********  FONCTIONS  ****************************************/

function affichage($user, $date_debut, $date_fin, $opt_debut, $opt_fin, $p_num="")
{
	$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
	$comment="&nbsp;" ;



	// calcul :
	$nb_jours=compter($user, $p_num, $date_debut, $date_fin, $opt_debut, $opt_fin, $comment);
	$tab['nb'] = $nb_jours;
	$tab['comm'] = $comment;

	echo json_encode($tab);
}
