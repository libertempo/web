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


	/*************************************/
	// recup des parametres reçus :
	
	$u_login = getpost_variable('u_login') ;
	$u_login_to_delete = getpost_variable('u_login_to_delete') ;
	/*************************************/

	// TITRE
	if($u_login!="")
		$login_titre = $u_login;
	elseif($u_login_to_delete!="")
		$login_titre = $u_login_to_delete;

	echo "<h1>". _('admin_suppr_user_titre') ." : <strong>$login_titre</strong></h1>\n";

	
	if($u_login!="")
	{
		confirmer($u_login, $onglet, $DEBUG);
	}
	elseif($u_login_to_delete!="")
	{
		suppression($u_login_to_delete, $DEBUG);
		redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-users', false);
		exit;
	}
	else
	{
		// renvoit sur la page principale .
		redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-users', false);
		exit;
	}


/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/

function confirmer($u_login, $onglet, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	/****************************/
	/* Etat Utilisateur en cours */
	/*****************************/
	// AFFICHAGE TABLEAU
	echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'&u_login_to_delete='.$u_login.'" method="POST">';
	echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
	echo '<thead>';
	echo '<tr>';
	echo "<th>". _('divers_login_maj_1') ."</th>\n";
	echo "<th>". _('divers_nom_maj_1') ."</th>\n";
	echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
	echo "</tr>\n";
	echo '</thead>';
	echo '<tbody>';

	// Récupération des informations
	$sql1 = 'SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login = "'. \includes\SQL::quote($u_login).'"';
	$ReqLog1 = \includes\SQL::query($sql1);

	echo "<tr>\n";
	while ($resultat1 = $ReqLog1->fetch_array())
	{
		echo "<td>".$resultat1["u_login"]."</td>\n";
		echo "<td>".$resultat1["u_nom"]."</td>\n";
		echo "<td>".$resultat1["u_prenom"]."</td>\n";
	}
	echo "</tr>\n";
	echo '</tbody>';
	echo "</table><br>\n\n";
	echo "<input class=\"btn btn-danger\" type=\"submit\" value=\"". _('form_supprim') ."\">\n";
	echo "<a class=\"btn\" href=\"admin_index.php?session=$session&onglet=admin-users\">". _('form_cancel') ."</a>\n";
	echo "</form>\n" ;

}

function suppression($u_login_to_delete, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();
	//echo($u_login_to_delete."---".$u_login_to_delete."<br>");

	$sql1 = 'DELETE FROM conges_users WHERE u_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
	$result = \includes\SQL::query($sql1);

	$sql2 = 'DELETE FROM conges_periode WHERE p_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
	$result2 = \includes\SQL::query($sql2);

	$sql3 = 'DELETE FROM conges_artt WHERE a_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
	$result3 = \includes\SQL::query($sql3);

	$sql4 = 'DELETE FROM conges_echange_rtt WHERE e_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
	$result4 = \includes\SQL::query($sql4);

	$sql5 = 'DELETE FROM conges_groupe_resp WHERE gr_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
	$result5 = \includes\SQL::query($sql5);

	$sql6 = 'DELETE FROM conges_groupe_users WHERE gu_login = "'. \includes\SQL::quote($u_login_to_delete).'"';
	$result6 = \includes\SQL::query($sql6);

	$sql7 = 'DELETE FROM conges_solde_user WHERE su_login = "'.\includes\SQL::quote($u_login_to_delete).'"';
	$result7 = \includes\SQL::query($sql7);


	$comment_log = "suppression_user ($u_login_to_delete)";
	log_action(0, "", $u_login_to_delete, $comment_log, $DEBUG);

	if($result)
		echo  _('form_modif_ok') ." !<br><br> \n" ;
	else
		echo  _('form_modif_not_ok') ." !<br><br> \n";
}

