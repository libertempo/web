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

$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;


	/*************************************/
	// recup des parametres reçus :

	$group 				= getpost_variable('group');
	$group_to_update 	= getpost_variable('group_to_update');
	$new_groupname 		= getpost_variable('new_groupname');
	$new_comment 		= getpost_variable('new_comment');
	$new_double_valid	= getpost_variable('new_double_valid');
	/*************************************/

	// TITRE
	echo "<h1>". _('admin_modif_groupe_titre') ."</h1>\n";


	if($group!="" )
	{
		modifier($group, $onglet, $DEBUG);
	}
	elseif($group_to_update!="")
	{
		commit_update($group_to_update, $new_groupname, $new_comment, $new_double_valid,  $DEBUG);
	}
	else
	{
		// renvoit sur la page principale .
		redirect( ROOT_PATH .'admin/admin_index.php?session='.$session.'&onglet=admin-group', false);
	}


/**************************************************************************************/
/**********  FONCTIONS  ***************************************************************/

function modifier($group, $onglet, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// Récupération des informations
	$sql1 = 'SELECT g_groupename, g_comment, g_double_valid FROM conges_groupe WHERE g_gid = \''.SQL::quote($group).'\'';

	// AFFICHAGE TABLEAU
	echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'&group_to_update='.$group.'" method="POST">';
	echo "<table class=\"table table-hover table-responsive table-condensed table-striped\" >\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th>". _('admin_groupes_groupe') ."</th>\n";
	echo "<th>". _('admin_groupes_libelle') ." / ". _('divers_comment_maj_1') ."</th>\n";
	if($_SESSION['config']['double_validation_conges'])
		echo "	<th>". _('admin_groupes_double_valid') ."</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";

	$ReqLog1 = SQL::query($sql1);
	while ($resultat1 = $ReqLog1->fetch_array())
	{
		$sql_groupename=$resultat1["g_groupename"];
		$sql_comment=$resultat1["g_comment"];
		$sql_double_valid=$resultat1["g_double_valid"] ;
	}


	// AFICHAGE DE LA LIGNE DES VALEURS ACTUELLES A MOFIDIER
	echo "<tr>\n";
	echo "<td>$sql_groupename</td>\n";
	echo "<td>$sql_comment</td>\n";
	if($_SESSION['config']['double_validation_conges'])
			echo "<td>$sql_double_valid</td>\n";
	echo "</tr>\n";

	// contruction des champs de saisie
	$text_group="<input class=\"form-control\" type=\"text\" name=\"new_groupname\" size=\"30\" maxlength=\"50\" value=\"".$sql_groupename."\">" ;
	$text_comment="<input class=\"form-control\" type=\"text\" name=\"new_comment\" size=\"50\" maxlength=\"200\" value=\"".$sql_comment."\">" ;

	// AFFICHAGE ligne de saisie
	echo "<tr>\n";
	echo "<td>$text_group</td>\n";
	echo "<td>$text_comment</td>\n";
	if($_SESSION['config']['double_validation_conges'])
	{
		$text_double_valid="<select class=\"form-control\" name=\"new_double_valid\" ><option value=\"N\" ";
		if($sql_double_valid=="N")
			$text_double_valid=$text_double_valid."SELECTED";
		$text_double_valid=$text_double_valid.">N</option><option value=\"Y\" ";
		if($sql_double_valid=="Y")
			$text_double_valid=$text_double_valid."SELECTED";
		$text_double_valid=$text_double_valid.">Y</option></select>" ;
		echo "<td>$text_double_valid</td>\n";
	}
	echo "</tr>\n";
	echo "</tbody>\n";

	echo "</table>";


	echo "<hr/>\n";
	echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
	echo "<a class=\"btn\" href=\"admin_index.php?session=$session&onglet=admin-group\">". _('form_cancel') ."</a>\n";
	echo "</form>\n" ;

}

function commit_update($group_to_update, $new_groupname, $new_comment, $new_double_valid,  $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	$result=TRUE;

	$new_comment=addslashes($new_comment);
	echo "$group_to_update---$new_groupname---$new_comment---$new_double_valid<br>\n";


	// UPDATE de la table conges_groupe
	$sql1 = 'UPDATE conges_groupe  SET g_groupename=\''.$new_groupname.'\', g_comment=\''.$new_comment.'\' , g_double_valid=\''.$new_double_valid.'\' WHERE g_gid=\''.SQL::quote($group_to_update).'\''  ;
	$result1 = SQL::query($sql1);
	if($result1==FALSE)
		$result==FALSE;


	$comment_log = "modif_groupe ($group_to_update) : $new_groupname , $new_comment (double_valid = $new_double_valid)";
	log_action(0, "", "", $comment_log,  $DEBUG);

	if($result)
		echo  _('form_modif_ok') ." !<br><br> \n";
	else
		echo  _('form_modif_not_ok') ." !<br><br> \n";

	/* APPEL D'UNE AUTRE PAGE au bout d'une tempo de 2secondes */
	echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=admin_index.php?session=$session&onglet=admin-group\">";

}


