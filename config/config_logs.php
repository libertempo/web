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

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$DEBUG = FALSE ;
//$DEBUG = TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);

if( $DEBUG ) { echo "SESSION = "; print_r($_SESSION); echo "<br>\n";}


	/*** initialisation des variables ***/
	/************************************/

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$action         = getpost_variable('action', "") ;
	$login_par      = getpost_variable('login_par', "") ;

	/*************************************/

	// header_menu('CONGES : Configuration', $_SESSION['config']['titre_admin_index']);


	if($action=="suppr_logs")
		confirmer_vider_table_logs($session, $DEBUG);
	elseif($action=="commit_suppr_logs")
		commit_vider_table_logs($session, $DEBUG);
	else
		affichage($login_par, $session, $DEBUG);


	// bottom();


/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/


function affichage($login_par, $session, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	//requête qui récupère les logs
	$sql1 = "SELECT log_user_login_par, log_user_login_pour, log_etat, log_comment, log_date FROM conges_logs ";
	if($login_par!="")
		$sql1 = $sql1." WHERE log_user_login_par = '$login_par' ";
	$sql1 = $sql1." ORDER BY log_date";

	$ReqLog1 = \includes\SQL::query($sql1);

	if($ReqLog1->num_rows !=0)
	{

		if($session=="")
			echo "<form action=\"$PHP_SELF?onglet=logs\" method=\"POST\"> \n";
		else
			echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\"> \n";

		echo "<br>\n";
		echo "<table class=\"table table-hover table-stripped table-condensed\">\n";

		echo "<tr><td class=\"histo\" colspan=\"5\">". _('voir_les_logs_par') ."</td>";
		if($login_par!="")
			echo "<tr><td class=\"histo\" colspan=\"5\">". _('voir_tous_les_logs') ." <a href=\"$PHP_SELF?session=$session&onglet=logs\">". _('voir_tous_les_logs') ."</a></td>";
		echo "<tr><td class=\"histo\" colspan=\"5\">&nbsp;</td>";

		// titres
		echo "<tr>\n";
		echo "<td>". _('divers_date_maj_1') ."</td>\n";
		echo "<td>". _('divers_fait_par_maj_1') ."</td>\n";
		echo "<td>". _('divers_pour_maj_1') ."</td>\n";
		echo "<td>". _('divers_comment_maj_1') ."</td>\n";
		echo "<td>". _('divers_etat_maj_1') ."</td>\n";
		echo "</tr>\n";

		// affichage des logs
		while ($data = $ReqLog1->fetch_array())
		{
			$log_login_par = $data['log_user_login_par'];
			$log_login_pour = $data['log_user_login_pour'];
			$log_log_etat = $data['log_etat'];
			$log_log_comment = $data['log_comment'];
			$log_log_date = $data['log_date'];

			echo "<tr>\n";
			echo "<td>$log_log_date</td>\n";
			echo "<td><a href=\"$PHP_SELF?session=$session&onglet=logs&login_par=$log_login_par\"><b>$log_login_par</b></a></td>\n";
			echo "<td>$log_login_pour</td>\n";
			echo "<td>$log_log_comment</td>\n";
			echo "<td>$log_log_etat</td>\n";
			echo "</tr>\n";
		}

		echo "</table>\n";

		// affichage du bouton pour vider les logs
		echo "<input type=\"hidden\" name=\"action\" value=\"suppr_logs\">\n";
		echo "<input class=\"btn btn-danger\" type=\"submit\"  value=\"". _('form_delete_logs') ."\"><br>";
		echo "</form>\n";
	}
	else
		echo  _('no_logs_in_db') ."><br>";

}


function confirmer_vider_table_logs($session, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
	$PHP_SELF=$_SERVER['PHP_SELF'];

	echo "<center>\n";
	echo "<br><h2>". _('confirm_vider_logs') ."</h2><br>\n";
	echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\">\n"  ;
	echo "<input type=\"hidden\" name=\"action\" value=\"commit_suppr_logs\">\n";
	echo "<input type=\"submit\" value=\"". _('form_delete_logs') ."\">\n";
	echo "</form>\n" ;
	echo "<form action=\"$PHP_SELF?session=$session&onglet=logs\" method=\"POST\">\n"  ;
	echo "<input type=\"submit\" value=\"". _('form_cancel') ."\">\n";
	echo "</form>\n" ;
	echo "</center>\n";

}

function commit_vider_table_logs($session, $DEBUG=FALSE)
{
//$DEBUG=TRUE;
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$sql_delete="TRUNCATE TABLE conges_logs ";
	$ReqLog_delete = \includes\SQL::query($sql_delete);

	// ecriture de cette action dans les logs
	$comment_log = "effacement des logs de php_conges ";
	log_action(0, "", "", $comment_log, $DEBUG);

	echo "<span class = \"messages\">". _('form_modif_ok') ."</span><br>";
	if($session=="")
		redirect( ROOT_PATH .'config/config_logs.php' );
	else
		redirect( ROOT_PATH .'config/config_logs.php?session='.$session );


}


