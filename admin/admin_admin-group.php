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


	$saisie_group   		= getpost_variable('saisie_group') ;
	$new_group_name			= addslashes( getpost_variable('new_group_name')) ;
	$new_group_libelle		= addslashes( getpost_variable('new_group_libelle')) ;
	$new_group_double_valid	= getpost_variable('new_group_double_valid') ;

	if($saisie_group=="ok")
	{
		ajout_groupe($new_group_name, $new_group_libelle, $new_group_double_valid,  $DEBUG);
	}
	else
	{
		affiche_gestion_groupes($new_group_name, $new_group_libelle, $onglet, $DEBUG);
	}

	
/*********************************************************************************/
/*  FONCTIONS   */
/*********************************************************************************/

function affiche_gestion_groupes($new_group_name, $new_group_libelle, $onglet, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	echo "<h1>". _('admin_onglet_gestion_groupe') ."</h1>\n\n";

	/*********************/
	/* Etat Groupes	   */
	/*********************/
	// Récuperation des informations :
	$sql_gr = "SELECT g_gid, g_groupename, g_comment, g_double_valid FROM conges_groupe ORDER BY g_groupename"  ;

	// AFFICHAGE TABLEAU
	echo "<h2>". _('admin_gestion_groupe_etat') ."</h2>\n";
	echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "    <th>". _('admin_groupes_groupe') ."</th>\n";
	echo "    <th>". _('admin_groupes_libelle') ."</th>\n";
	echo "    <th>". _('admin_groupes_nb_users') ."</th>\n";
	if($_SESSION['config']['double_validation_conges'])
		 echo "    <th>". _('admin_groupes_double_valid') ."</th>\n";
	echo "    <th></th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";

	$i = true;
	$ReqLog_gr = \includes\SQL::query($sql_gr);
	while ($resultat_gr = $ReqLog_gr->fetch_array())
	{
		 $sql_gid=$resultat_gr["g_gid"] ;
		 $sql_group=$resultat_gr["g_groupename"] ;
		 $sql_comment=$resultat_gr["g_comment"] ;
		 $sql_double_valid=$resultat_gr["g_double_valid"] ;
		 $nb_users_groupe = get_nb_users_du_groupe($sql_gid, $DEBUG);

		 $admin_modif_group="<a href=\"admin_index.php?onglet=modif_group&session=$session&group=$sql_gid\" title=\"". _('form_modif') ."\"><i class=\"fa fa-pencil\"></i></a>" ;
		 $admin_suppr_group="<a href=\"admin_index.php?onglet=suppr_group&session=$session&group=$sql_gid\" title=\"". _('form_supprim') ."\"><i class=\"fa fa-times-circle\"></i></a>" ;

		 echo '<tr class="'.($i?'i':'p').'">';
		 echo "<td><b>$sql_group</b></td>\n";
		 echo "<td>$sql_comment</td>\n";
		 echo "<td>$nb_users_groupe</td>\n";
		 if($_SESSION['config']['double_validation_conges'])
		 	echo "<td>$sql_double_valid</td>\n";
		 echo "<td class=\"action\">$admin_modif_group $admin_suppr_group</td>\n";
		 echo "</tr>\n";
		$i = !$i;
	}
	echo "</tbody>\n\n";
	echo "</table>\n\n";

	echo "<hr/>\n";

	/*********************/
	/* Ajout Groupe      */
	/*********************/

	// TITRE
  
	echo "<h2>". _('admin_groupes_new_groupe') ."</h2>\n";
	echo '<form action="'.$PHP_SELF.'?session='.$session.'&onglet='.$onglet.'" method="POST">';
	echo "<table class=\"tablo\">\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th><b>". _('admin_groupes_groupe') ."</b></th>\n";
	echo "<th>". _('admin_groupes_libelle') ." / ". _('divers_comment_maj_1') ."</th>\n";
	if($_SESSION['config']['double_validation_conges'])
		echo "    <th>". _('admin_groupes_double_valid') ."</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";

	$text_groupname="<input class=\"form-control\" type=\"text\" name=\"new_group_name\" size=\"30\" maxlength=\"50\" value=\"".$new_group_name."\">" ;
	$text_libelle="<input class=\"form-control\" type=\"text\" name=\"new_group_libelle\" size=\"50\" maxlength=\"250\" value=\"".$new_group_libelle."\">" ;

	echo "<tr>\n";
	echo "<td>$text_groupname</td>\n";
	echo "<td>$text_libelle</td>\n";
	if($_SESSION['config']['double_validation_conges'])
	{
		 $text_double_valid="<select class=\"form-control\" name=\"new_group_double_valid\" ><option value=\"N\">N</option><option value=\"Y\">Y</option></select>" ;
		 echo "<td>$text_double_valid</td>\n";
	}
	echo "</tr>\n";
	echo "</tbody>\n";
	echo "</table>";

	echo "<hr>\n";
	echo "<input type=\"hidden\" name=\"saisie_group\" value=\"ok\">\n";
	echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n";
	// echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session&onglet=admin-group\">". _('form_cancel') ."</a>\n";
	echo "</form>\n" ;

}



function ajout_groupe($new_group_name, $new_group_libelle, $new_group_double_valid,  $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	if(verif_new_param_group($new_group_name, $new_group_libelle,  $DEBUG)==0)  // verif si les nouvelles valeurs sont coohérentes et n'existe pas déjà
	{
		$ngm=stripslashes($new_group_name);
		echo "$ngm --- $new_group_libelle<br>\n";

		$sql1 = "INSERT INTO conges_groupe SET g_groupename='$new_group_name', g_comment='$new_group_libelle', g_double_valid ='$new_group_double_valid' " ;
		$result = \includes\SQL::query($sql1);

		$new_gid= \includes\SQL::getVar('insert_id');
		// par défaut le responsable virtuel est resp de tous les groupes !!!
		// $sql2 = "INSERT INTO conges_groupe_resp SET gr_gid=$new_gid, gr_login='conges' " ;
		// $result = SQL::query($sql2);

		if($result)
			echo  _('form_modif_ok') ."<br><br> \n";
		else
			echo  _('form_modif_not_ok') ."<br><br> \n";

		$comment_log = "ajout_groupe : $new_gid / $new_group_name / $new_group_libelle (double_validation : $new_group_double_valid)" ;
		log_action(0, "", "", $comment_log, $DEBUG);

		/* APPEL D'UNE AUTRE PAGE */
		echo " <form action=\"$PHP_SELF?session=$session&onglet=admin-group\" method=\"POST\"> \n";
		echo "<input type=\"submit\" value=\"". _('form_retour') ."\">\n";
		echo " </form> \n";
	}
}


function verif_new_param_group($new_group_name, $new_group_libelle, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// verif des parametres reçus :
	if(strlen($new_group_name)==0) {
		echo "<H3> ". _('admin_verif_param_invalides') ." </H3>\n" ;
		echo "$new_group_name --- $new_group_libelle<br>\n";
		echo "<form action=\"$PHP_SELF?session=$session&onglet=admin-group\" method=\"POST\">\n" ;
		echo "<input type=\"hidden\" name=\"new_group_name\" value=\"$new_group_name\">\n";
		echo "<input type=\"hidden\" name=\"new_group_libelle\" value=\"$new_group_libelle\">\n";

		echo "<input type=\"hidden\" name=\"saisie_group\" value=\"faux\">\n";
		echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
		echo "</form>\n" ;

		return 1;
	}
	else {
		// verif si le groupe demandé n'existe pas déjà ....
		$sql_verif='select g_groupename from conges_groupe where g_groupename="'. \includes\SQL::quote($new_group_name).'" ';
		$ReqLog_verif = \includes\SQL::query($sql_verif);
		$num_verif = $ReqLog_verif->num_rows;
		if ($num_verif!=0)
		{
			echo "<H3> ". _('admin_verif_groupe_invalide') ." </H3>\n" ;
			echo "<form action=\"$PHP_SELF?session=$session&onglet=admin-group\" method=\"POST\">\n" ;
			echo "<input type=\"hidden\" name=\"new_group_name\" value=\"$new_group_name\">\n";
			echo "<input type=\"hidden\" name=\"new_group_libelle\" value=\"$new_group_libelle\">\n";

			echo "<input type=\"hidden\" name=\"saisie_group\" value=\"faux\">\n";
			echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
			echo "</form>\n" ;

			return 1;
		}
		else
			return 0;
	}
}


// recup le nombre de users d'un groupe donné
function get_nb_users_du_groupe($group_id,  $DEBUG=FALSE)
{

	$sql1='SELECT DISTINCT(gu_login) FROM conges_groupe_users WHERE gu_gid = '. \includes\SQL::quote($group_id).' ORDER BY gu_login ';
	$ReqLog1 = \includes\SQL::query($sql1);

	$nb_users = $ReqLog1->num_rows;

	return $nb_users;

}

