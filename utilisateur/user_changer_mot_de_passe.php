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


if($_SESSION['config']['where_to_find_user_email']=="ldap"){ include CONFIG_PATH .'config_ldap.php';}


	$change_passwd = getpost_variable('change_passwd', 0);
	$new_passwd1 = getpost_variable('new_passwd1');
	$new_passwd2 = getpost_variable('new_passwd2');


	
	if($change_passwd==1) {
		change_passwd($new_passwd1, $new_passwd2, $DEBUG);
	}
	else {
		$PHP_SELF=$_SERVER['PHP_SELF'];
		$session=session_id();

	
	
		echo '<h1>'. _('user_change_password') .'</h1>';

		echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';
		echo '<table cellpadding="2" class="tablo" width="500">';
		echo '<thead>';
/*
		echo '<tr>
				<td class="titre">'. _('user_passwd_saisie_1') .'</td>
				<td class="titre">'. _('user_passwd_saisie_2') .'</td>
			</tr>';
*/
		echo '<tr>
				<th class="titre">'. _('user_passwd_saisie_1') .'</th>
				<th class="titre">'. _('user_passwd_saisie_2') .'</th>
			</tr>';
		echo '</thead>';
		echo '<tbody>';
		
		$text_passwd1	= '<input class="form-control" type="password" name="new_passwd1" size="10" maxlength="20" value="">';
		$text_passwd2	= '<input class="form-control" type="password" name="new_passwd2" size="10" maxlength="20" value="">';
		echo '<tr>';
		echo '<td>'.($text_passwd1).'</td><td>'.($text_passwd2).'</td>'."\n";
		echo '</tr>';

		echo '</tbody>';
		echo '</table>';

		echo "<hr/>\n";
		echo '<input type="hidden" name="change_passwd" value=1>';
		echo '<input class="btn btn-success" type="submit" value="'. _('form_submit') .'">';
		echo '</form>';
	}




/**************************************************************************************/
/********  FONCTIONS      ******/
/**************************************************************************************/



function change_passwd( $new_passwd1, $new_passwd2, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	if((strlen($new_passwd1)==0) || (strlen($new_passwd2)==0) || ($new_passwd1!=$new_passwd2)) // si les 2 passwd sont vides ou differents
	{
		echo  _('user_passwd_error') ."<br>\n" ;
	}
	else
	{
		$passwd_md5=md5($new_passwd1);
		$sql1 = 'UPDATE conges_users SET  u_passwd=\''.$passwd_md5.'\' WHERE u_login=\''.$_SESSION['userlogin'].'\' ';
		$result = \includes\SQL::query($sql1) ;

		if($result)
			echo  _('form_modif_ok') ." <br><br> \n";
		else
			echo  _('form_mofif_not_ok') ."<br><br> \n";
	}

	$comment_log = 'changement Password';
	log_action(0, '', $_SESSION['userlogin'], $comment_log,  $DEBUG);

}

