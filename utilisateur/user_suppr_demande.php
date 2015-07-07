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


	$p_num           = getpost_variable('p_num');
	$onglet          = getpost_variable('onglet');
	$p_num_to_delete = getpost_variable('p_num_to_delete');
	/*************************************/

	// TITRE
	echo '<h1>'. _('user_suppr_demande_titre') .'</h1>';
	echo "<br> \n";

	if($p_num!="")
	{
		confirmer($p_num, $onglet, $DEBUG);
	}
	else
	{
		if($p_num_to_delete!="")
		{
			suppression($p_num_to_delete, $onglet, $DEBUG);
		}
		else
		{
			// renvoit sur la page principale .
			redirect( ROOT_PATH .'utilisateur/user_index.php', false );
		}
	}

	
/************************************************************************************************/
/*** fonctions    ***/
/************************************************************************************************/

function confirmer($p_num, $onglet, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;


	// Récupération des informations
	$sql1 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_type, p_num FROM conges_periode WHERE p_num = \''.SQL::quote($p_num).'\'';
	//printf("sql1 = %s<br>\n", $sql1);
	$ReqLog1 = SQL::query($sql1) ;

	// AFFICHAGE TABLEAU
	echo "<form action=\"$PHP_SELF\" method=\"POST\">\n"  ;
	echo "<table class=\"table table-responsive table-condensed\">\n";
	echo "<thead>\n";
	echo "<tr>\n";
	echo "<th>". _('divers_debut_maj_1') ."</th>\n";
	echo "<th>". _('divers_fin_maj_1') ."</th>\n";
	echo "<th>". _('divers_nb_jours_maj_1') ."</th>\n";
	echo "<th>". _('divers_comment_maj_1') ."</th>\n";
	echo "<th>". _('divers_type_maj_1') ."</th>\n";
	echo "</tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";
	echo "<tr>\n";
	while ($resultat1 = $ReqLog1->fetch_array())
	{
		$sql_date_deb=eng_date_to_fr($resultat1["p_date_deb"]);
		$sql_demi_jour_deb = $resultat1["p_demi_jour_deb"];
		if($sql_demi_jour_deb=="am")
			$demi_j_deb= _('divers_am_short') ;
		else
			$demi_j_deb= _('divers_pm_short') ;
		$sql_date_fin=eng_date_to_fr($resultat1["p_date_fin"]);
		$sql_demi_jour_fin = $resultat1["p_demi_jour_fin"];
		if($sql_demi_jour_fin=="am")
			$demi_j_fin= _('divers_am_short') ;
		else
			$demi_j_fin= _('divers_pm_short') ;
		$sql_nb_jours=affiche_decimal($resultat1["p_nb_jours"]);
		//$sql_type=$resultat1["p_type"];
		$sql_type=get_libelle_abs($resultat1["p_type"], $DEBUG);
		$sql_comment=$resultat1["p_commentaire"];

		if( $DEBUG ) { echo "$sql_date_deb _ $demi_j_deb : $sql_date_fin _ $demi_j_fin : $sql_nb_jours : $sql_comment : $sql_type<br>\n"; }

		echo "<td>$sql_date_deb _ $demi_j_deb</td>\n";
		echo "<td>$sql_date_fin _ $demi_j_fin</td>\n";
		echo "<td>$sql_nb_jours</td>\n";
		echo "<td>$sql_comment</td>\n";
		echo "<td>$sql_type</td>\n";
	}
	echo "</tr>\n";
	echo "</tbody>\n";
	echo "</table>\n";
	echo "<hr/>\n";
	echo "<input type=\"hidden\" name=\"p_num_to_delete\" value=\"$p_num\">\n";
	echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n";
	echo "<input type=\"hidden\" name=\"onglet\" value=\"$onglet\">\n";
	echo "<input class=\"btn btn-danger\" type=\"submit\" value=\"". _('form_supprim') ."\">\n";
	echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session&onglet=demandes_en_cours\">". _('form_cancel') ."</a>\n";
	echo "</form>\n" ;

}

function suppression($p_num_to_delete, $onglet, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;

	$sql_delete = 'DELETE FROM conges_periode WHERE p_num = '.SQL::quote($p_num_to_delete).' AND p_login=\''.SQL::quote($_SESSION['userlogin']).'\';';

	$result_delete = SQL::query($sql_delete);

	$comment_log = "suppression de demande num $p_num_to_delete";
	log_action($p_num_to_delete, "", $_SESSION['userlogin'], $comment_log, $DEBUG);

	if($result_delete)
		echo  _('form_modif_ok') ."<br><br> \n";
	else
		echo  _('form_modif_not_ok') ."<br><br> \n";

	/* APPEL D'UNE AUTRE PAGE */
	echo '<form action="'.ROOT_PATH .'utilisateur/user_index.php?session='.$session.'&onglet=demandes_en_cours" method="POST">';
		echo '<input class="btn" type="submit" value="'. _('form_submit') .'">';
	echo '</form>';
	echo '<a href="">';

}


// renvoit le libelle d une absence (conges ou absence) d une absence
function get_libelle_abs($_type_abs_id,  $DEBUG=FALSE)
{

	$sql_abs='SELECT ta_libelle FROM conges_type_absence WHERE ta_id=\''.SQL::quote($_type_abs_id).'\'';
	$ReqLog_abs = SQL::query($sql_abs);
	if($resultat_abs = $ReqLog_abs->fetch_array())
		return $resultat_abs['ta_libelle'];
	else
		return "" ;
}
