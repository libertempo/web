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

	$user_login		= $_SESSION['userlogin'];
	$p_num             = getpost_variable('p_num');
	$onglet            = getpost_variable('onglet');
	$p_num_to_update   = getpost_variable('p_num_to_update');
	$p_etat			   = getpost_variable('p_etat');
	$new_debut         = getpost_variable('new_debut');
	$new_demi_jour_deb = getpost_variable('new_demi_jour_deb');
	$new_fin           = getpost_variable('new_fin');
	$new_demi_jour_fin = getpost_variable('new_demi_jour_fin');
	$new_comment       = getpost_variable('new_comment');

	//conversion des dates
	$new_debut = convert_date($new_debut);
	$new_fin = convert_date($new_fin); 

	if ($_SESSION['config']['disable_saise_champ_nb_jours_pris'])
		$new_nb_jours = compter($user_login, $p_num_to_update, $new_debut,  $new_fin, $new_demi_jour_deb, $new_demi_jour_fin, $new_comment,  $DEBUG);
	else
		$new_nb_jours = getpost_variable('new_nb_jours');
		
	/*************************************/

	// TITRE
	echo '<h1>'. _('user_modif_demande_titre') .'</h1>';

	if($p_num!="")
	{
		confirmer($p_num, $onglet, $DEBUG);
	}
	else
	{
		if($p_num_to_update != "")
		{
			modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet, $DEBUG);
		}
		else
		{
			// renvoit sur la page principale .
			redirect( ROOT_PATH .'utilisateur/user_index.php', false );
		}
	}


/********************************************************************************************************/
/********************************************************************************************************/
/********************************************************************************************************/
function confirmer($p_num, $onglet, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();


	// Récupération des informations
	$sql1 = 'SELECT p_login, p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_nb_jours, p_commentaire, p_etat, p_num FROM conges_periode where p_num = \''.SQL::quote($p_num).'\'';
	$ReqLog1 = SQL::query($sql1) ;

	// AFFICHAGE TABLEAU
	include ROOT_PATH .'fonctions_javascript.php' ;
	echo '<form NAME="dem_conges" action="'.$PHP_SELF.'" method="POST">' ;
	echo "<table class=\"table table-responsive\">\n" ;
	echo '<thead>';
	// affichage première ligne : titres
	echo "<tr>\n";
	echo "<td>". _('divers_debut_maj_1') ."</td>\n";
	echo "<td>". _('divers_fin_maj_1') ."</td>\n";
	echo "<td>". _('divers_nb_jours_maj_1') ."</td>\n";
	echo "<td>". _('divers_comment_maj_1') ."</td>\n";
	echo "</tr>\n" ;
	echo '</thead>';
	echo '<tbody>';
	// affichage 2ieme ligne : valeurs actuelles
	echo "<tr>\n" ;
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
		$sql_nb_jours=$resultat1["p_nb_jours"];
		$aff_nb_jours=affiche_decimal($sql_nb_jours);
		$sql_commentaire=$resultat1["p_commentaire"];
		$sql_etat=$resultat1["p_etat"];

		echo "<td>$sql_date_deb _ $demi_j_deb</td><td>$sql_date_fin _ $demi_j_fin</td><td>$aff_nb_jours</td><td>$sql_commentaire</td>\n" ;

		$compte ="";
		if($_SESSION['config']['rempli_auto_champ_nb_jours_pris'])
		{
			$compte = 'onChange="compter_jours();return false;"';
		}

		$text_debut="<input class=\"form-control date\" type=\"text\" name=\"new_debut\" size=\"10\" maxlength=\"30\" value=\"" . revert_date($sql_date_deb) . "\">" ;
		if($sql_demi_jour_deb=="am")
		{
			$radio_deb_am="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"am\" checked>&nbsp;". _('form_am') ;
			$radio_deb_pm="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"pm\">&nbsp;". _('form_pm') ;
		}
		else
		{
			$radio_deb_am="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"am\">". _('form_am') ;
			$radio_deb_pm="<input type=\"radio\" $compte name=\"new_demi_jour_deb\" value=\"pm\" checked>". _('form_pm') ;
		}
		$text_fin="<input class=\"form-control date\" type=\"text\" name=\"new_fin\" size=\"10\" maxlength=\"30\" value=\"" . revert_date($sql_date_fin) . "\">" ;
		if($sql_demi_jour_fin=="am")
		{
			$radio_fin_am="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"am\" checked>". _('form_am') ;
			$radio_fin_pm="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"pm\">". _('form_pm') ;
		}
		else
		{
			$radio_fin_am="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"am\">". _('form_am') ;
			$radio_fin_pm="<input type=\"radio\" $compte name=\"new_demi_jour_fin\" value=\"pm\" checked>". _('form_pm') ;
		}
		if($_SESSION['config']['disable_saise_champ_nb_jours_pris'])
			$text_nb_jours="<input class=\"form-control\" type=\"text\" name=\"new_nb_jours\" size=\"5\" maxlength=\"30\" value=\"$sql_nb_jours\" style=\"background-color: #D4D4D4; \" readonly=\"readonly\">" ;
		else
			$text_nb_jours="<input class=\"form-control\" type=\"text\" name=\"new_nb_jours\" size=\"5\" maxlength=\"30\" value=\"$sql_nb_jours\">" ;
		
		
		$text_commentaire="<input class=\"form-control\" type=\"text\" name=\"new_comment\" size=\"15\" maxlength=\"30\" value=\"$sql_commentaire\">" ;
	}
	echo "</tr>\n";

	// affichage 3ieme ligne : saisie des nouvelles valeurs
	echo "<tr>\n" ;
	echo "<td>$text_debut<br>$radio_deb_am / $radio_deb_pm</td><td>$text_fin<br>$radio_fin_am / $radio_fin_pm</td><td>$text_nb_jours</td><td>$text_commentaire</td>\n" ;
	echo "</tr>\n" ;

	echo '</tbody>';
	echo "</table>\n" ;
	echo '<hr/>';
	echo "<input type=\"hidden\" name=\"p_num_to_update\" value=\"$p_num\">\n" ;
	echo "<input type=\"hidden\" name=\"p_etat\" value=\"$sql_etat\">\n" ;
	echo "<input type=\"hidden\" name=\"session\" value=\"$session\">\n" ;
	echo '<input type="hidden" name="user_login" value="'.$_SESSION['userlogin'].'">';
	echo "<input type=\"hidden\" name=\"onglet\" value=\"$onglet\">\n" ;
	echo '<p id="comment_nbj" style="color:red">&nbsp;</p>';
	echo "<input class=\"btn btn-success\" type=\"submit\" value=\"". _('form_submit') ."\">\n" ;
	echo "<a class=\"btn\" href=\"$PHP_SELF?session=$session&onglet=demandes_en_cours\">". _('form_cancel') ."</a>\n";
	echo "</form>\n" ;

}


function modifier($p_num_to_update, $new_debut, $new_demi_jour_deb, $new_fin, $new_demi_jour_fin, $new_nb_jours, $new_comment, $p_etat, $onglet, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id() ;

	// echo($new_debut." / ".$new_demi_jour_deb."---".$new_fin." / ".$new_demi_jour_fin."---".$new_nb_jours."---".$new_comment."<br>");
	// echo (string) $new_nb_jours;
	// exit;

	$sql1 = "UPDATE conges_periode
		SET p_date_deb='$new_debut', p_demi_jour_deb='$new_demi_jour_deb', p_date_fin='$new_fin', p_demi_jour_fin='$new_demi_jour_fin', p_nb_jours='$new_nb_jours', p_commentaire='$new_comment', ";
	if($p_etat=="demande")
		 $sql1 = $sql1." p_date_demande=NOW() ";
	else
		 $sql1 = $sql1." p_date_traitement=NOW() ";
	$sql1 = $sql1."	WHERE p_num='$p_num_to_update' AND p_login='".$_SESSION['userlogin']."' ;" ;

	$result = SQL::query($sql1) ;

	$comment_log = "modification de demande num $p_num_to_update ($new_nb_jours jour(s)) ( de $new_debut $new_demi_jour_deb a $new_fin $new_demi_jour_fin) ($new_comment)";
	log_action($p_num_to_update, "$p_etat", $_SESSION['userlogin'], $comment_log, $DEBUG);


	echo  _('form_modif_ok') ."<br><br> \n" ;
	/* APPEL D'UNE AUTRE PAGE */
	echo '<form action="'.ROOT_PATH .'utilisateur/user_index.php?session='.$session.'&onglet=demandes_en_cours" method="POST">';
		echo '<input class="btn" type="submit" value="'. _('form_submit') .'">';
	echo '</form>';

}

