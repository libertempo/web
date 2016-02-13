<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)
Copyright (C) 2015 (Prytoegrian)
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/

define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';

$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include_once ROOT_PATH . 'fonctions_conges.php' ;
include_once INCLUDE_PATH . 'fonction.php';
include_once INCLUDE_PATH . 'session.php';
include_once ROOT_PATH . 'fonctions_calcul.php';

/*** initialisation des variables ***/
$session=session_id();
$user="";
$date_debut="";
$date_fin="";
$p_num="";

/*************************************/
// recup des parametres reçus :
// SERVER
$PHP_SELF=$_SERVER['PHP_SELF'];
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
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	$comment="&nbsp;" ;



	// calcul :
	$nb_jours=compter($user, $p_num, $date_debut, $date_fin, $opt_debut, $opt_fin, $comment);
	$tab['nb'] = $nb_jours;
	$tab['comm'] = $comment;
	if(!$_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
	{
		$tab['nb'] = "";
	}
		echo json_encode($tab);
}



