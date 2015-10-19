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
	// SERVER
	
	$u_login            = getpost_variable('u_login') ;
	$u_login_to_update  = getpost_variable('u_login_to_update') ;
	$new_pwd1           = getpost_variable('new_pwd1') ;
	$new_pwd2           = getpost_variable('new_pwd2') ;
	/*************************************/

	
	if($u_login!="")
	{
		echo "<H1>". _('admin_chg_passwd_titre') ." : $u_login .</H1>\n\n";
		modifier($u_login, $onglet, $DEBUG);
	}
	else
	{
		if($u_login_to_update!="") {
			echo "<H1>". _('admin_chg_passwd_titre') ." : $u_login_to_update .</H1>\n\n";
			commit_update($u_login_to_update, $new_pwd1, $new_pwd2, $DEBUG);
		}
		else {
			// renvoit sur la page principale .
			redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-users', false);
		}
	}



/*********************************************************************************/
/*  FONCTIONS   */
/*********************************************************************************/

function modifier($u_login, $onglet, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	/********************/
	/* Etat utilisateur */
	/********************/
	// AFFICHAGE TABLEAU
	echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'&u_login_to_update='.$u_login.'" method="POST">';
	echo "<table cellpadding=\"2\" class=\"tablo\" width=\"80%\">\n";
	echo '<thead>';
	echo '<tr>';
	echo "<th>". _('divers_login_maj_1') ."</th>\n";
	echo "<th>". _('divers_nom_maj_1') ."</th>\n";
	echo "<th>". _('divers_prenom_maj_1') ."</th>\n";
	echo "<th>". _('admin_users_password_1') ."</th>\n";
	echo "<th>". _('admin_users_password_2') ."</th>\n";
	echo "</tr>\n";
	echo '</thead>';
	echo '<tbody>';

	echo "<tr align=\"center\">\n";

	// Récupération des informations
	$sql1 = 'SELECT u_login, u_nom, u_prenom FROM conges_users WHERE u_login = \''.SQL::quote($u_login).'\'';
	$ReqLog1 = SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array()) {
			$text_pwd1="<input type=\"password\" name=\"new_pwd1\" size=\"10\" maxlength=\"30\" value=\"\">" ;
			$text_pwd2="<input type=\"password\" name=\"new_pwd2\" size=\"10\" maxlength=\"30\" value=\"\">" ;
			echo  "<td>".$resultat1["u_login"]."</td><td>".$resultat1["u_nom"]."</td><td>".$resultat1["u_prenom"]."</td><td>$text_pwd1</td><td>$text_pwd2</td>\n";
		}
	echo "<tr>\n";
	echo '<tbody>';
	echo "</table>\n\n";
	echo "<input type=\"submit\" value=\"". _('form_submit') ."\">\n";
	echo "</form>\n" ;

	echo "<form action=\"admin_index.php?session=$session&onglet=admin-users\" method=\"POST\">\n" ;
	echo "<input type=\"submit\" value=\"". _('form_cancel') ."\">\n";
	echo "</form>\n"  ;

}

function commit_update($u_login_to_update, $new_pwd1, $new_pwd2, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	if( (strlen($new_pwd1)!=0) && (strlen($new_pwd2)!=0) && (strcmp($new_pwd1, $new_pwd2)==0) )
	{

		$passwd_md5=md5($new_pwd1);
		$sql1 = 'UPDATE conges_users  SET u_passwd=\''.$passwd_md5.'\' WHERE u_login=\''.SQL::quote($u_login_to_update).'\'' ;
		$result = SQL::query($sql1);

		if($result)
			echo  _('form_modif_ok') ." !<br><br> \n";
		else
			echo  _('form_modif_not_ok') ." !<br><br> \n";

		$comment_log = "admin_change_password_user : pour $u_login_to_update" ;
		log_action(0, "", $u_login_to_update, $comment_log, $DEBUG);

		if( $DEBUG )
		{
			echo "<form action=\"admin_index.php?session=$session&onglet=admin-users\" method=\"POST\">\n" ;
			echo "<input type=\"submit\" value=\"". _('form_ok') ."\">\n";
			echo "</form>\n" ;
		}
		else
		{
			/* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
			echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=admin_index.php?session=$session&onglet=admin-users\">";
		}

	}
	else
	{
	 	echo "<H3> ". _('admin_verif_param_invalides') ." </H3>\n" ;
		echo "<form action=\"$PHP_SELF?session=$session&onglet=chg_pwd_user\" method=\"POST\">\n" ;
		echo "<input type=\"hidden\" name=\"u_login\" value=\"$u_login_to_update\">\n";

		echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
		echo "</form>\n" ;
	}

}

