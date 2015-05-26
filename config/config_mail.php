<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
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

// define('_PHP_CONGES', 1);
// define('ROOT_PATH', '../');
// include ROOT_PATH . 'define.php';
// defined( '_PHP_CONGES' ) or die( 'Restricted access' );

// $session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : "") ) ;

// if (file_exists(CONFIG_PATH .'config_ldap.php'))
// 	include CONFIG_PATH .'config_ldap.php';
	
// include ROOT_PATH .'fonctions_conges.php' ;
// include INCLUDE_PATH .'fonction.php';
// if(!isset($_SESSION['config']))
// 	$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
// include INCLUDE_PATH .'session.php';
// $session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : "") ) ;


//$DEBUG = TRUE ;
$DEBUG = FALSE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);



	/*** initialisation des variables ***/
	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$action = getpost_variable('action') ;
	$tab_new_values = getpost_variable('tab_new_values');

	/*************************************/

	if($DEBUG)
	{
		print_r($tab_new_values); echo "<br>\n";
		echo "$action<br>\n";
	}

	// header_menu('CONGES : Configuration', $_SESSION['config']['titre_admin_index']);
	
	/*********************************/
	/*********************************/

	if($action=="modif")
		commit_modif($tab_new_values, $session, $DEBUG);

	affichage($tab_new_values, $session, $DEBUG);

	/*********************************/
	/*********************************/
	
	// bottom();




/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/


function affichage($tab_new_values, $session, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	if($session=="")
		$URL = "$PHP_SELF?onglet=mail";
	else
		$URL = "$PHP_SELF?session=$session&onglet=mail";

	/**************************************/
	// affichage du titre
	echo "<div class=\"alert alert-info\"> ". _('config_mail_alerte_config') ."</div>\n";
	/**************************************/

	// affichage de la liste des type d'absence existants

	//requête qui récupère les informations de la table conges_type_absence
	$sql1 = "SELECT * FROM conges_mail ";
	$ReqLog1 = SQL::query($sql1);

	echo "    <form action=\"$URL\" method=\"POST\"> \n";
	while ($data = $ReqLog1->fetch_array())
	{
	 	$mail_nom = stripslashes($data['mail_nom']);
		$mail_subject = stripslashes($data['mail_subject']);
		$mail_body = stripslashes($data['mail_body']);

		$legend =$mail_nom ;
		// echo $mail_nom ;
		$key = $mail_nom."_comment";
		$comment =  _($key)  ;

		echo "<br>\n";
		echo "<table>\n";
		echo "<tr><td>\n";
		echo "    <fieldset class=\"cal_saisie\">\n";
		echo "    <legend class=\"boxlogin\">$legend</legend>\n";
		echo "    <i>$comment</i><br><br>\n";
		echo "    <table>\n";
		echo "    <tr>\n";
		echo "    	<td class=\"config\" valign=\"top\"><b>". _('config_mail_subject') ."</b></td>\n";
		echo "    	<td class=\"config\"><input class=\"form-control\" type=\"text\" size=\"80\" name=\"tab_new_values[$mail_nom][subject]\" value=\"$mail_subject\"></td>\n";
		echo "    </tr>\n";
		echo "    <tr>\n";
		echo "    	<td class=\"config\" valign=\"top\"><b>". _('config_mail_body') ."</b></td>\n";
		echo "    	<td class=\"config\"><textarea class=\"form-control\" rows=\"6\" cols=\"80\" name=\"tab_new_values[$mail_nom][body]\" value=\"$mail_body\">$mail_body</textarea></td>\n";
		echo "    </tr>\n";
		echo "    <tr>\n";
		echo "    	<td class=\"config\">&nbsp;</td>\n";
		echo "    	<td class=\"config\">\n";
		echo "    		<i>". _('mail_remplace_url_accueil_comment') ."<br>\n";
		echo "    		". _('mail_remplace_sender_name_comment') ."<br>\n";
		echo "    		". _('mail_remplace_destination_name_comment') ."<br>\n";
		echo "    		". _('mail_remplace_nb_jours') ."<br>\n";
		echo "    		". _('mail_remplace_date_debut') ."<br>\n";
		echo "    		". _('mail_remplace_date_fin') ."<br>\n";
		echo "    		". _('mail_remplace_commentaire') ."<br>\n";
		echo "    		". _('mail_remplace_type_absence') ."<br>\n";
		echo "    		". _('mail_remplace_retour_ligne_comment') ."</i>\n";
		echo "    	</td>\n";
		echo "    </tr>\n";

		echo "    </table>\n";
		echo "</td></tr>\n";
		echo "</table>\n";
	}

	echo "    <input type=\"hidden\" name=\"action\" value=\"modif\">\n";
	echo "<hr/>\n";
	echo "    <input class=\"btn btn-success\" type=\"submit\"  value=\"". _('form_save_modif') ."\"><br>\n";
	echo "    </form>\n";

}

function commit_modif($tab_new_values, $session, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];

	if($session=="")
		$URL = "$PHP_SELF?onglet=mail";
	else
		$URL = "$PHP_SELF?session=$session&onglet=mail";


	// update de la table
	foreach($tab_new_values as $nom_mail => $tab_mail)
	{
		$subject = addslashes($tab_mail['subject']);
		$body = addslashes($tab_mail['body']) ;
		$req_update='UPDATE conges_mail SET mail_subject=\''.$subject.'\', mail_body=\''.$body.'\' WHERE mail_nom=\''.SQL::quote($nom_mail).'\' ';
		$result1 = SQL::query($req_update);
	}
	echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";

	$comment_log = "configuration des mails d\'alerte";
	log_action(0, "", "", $comment_log, $DEBUG);

	if( $DEBUG )
		echo "<a href=\"$URL\" method=\"POST\">". _('form_retour') ."</a><br>\n" ;
	else
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=$URL\">";

}

