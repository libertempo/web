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


// saisie de la grille des jours d'abscence ARTT ou temps partiel:
function saisie_jours_absence_temps_partiel($login,  $DEBUG=FALSE)
{

	/* initialisation des variables **************/
	$checked_option_sem_imp_lu_am='';
	$checked_option_sem_imp_lu_pm='';
	$checked_option_sem_imp_ma_am='';
	$checked_option_sem_imp_ma_pm='';
	$checked_option_sem_imp_me_am='';
	$checked_option_sem_imp_me_pm='';
	$checked_option_sem_imp_je_am='';
	$checked_option_sem_imp_je_pm='';
	$checked_option_sem_imp_ve_am='';
	$checked_option_sem_imp_ve_pm='';
	$checked_option_sem_imp_sa_am='';
	$checked_option_sem_imp_sa_pm='';
	$checked_option_sem_imp_di_am='';
	$checked_option_sem_imp_di_pm='';

	$checked_option_sem_p_lu_am='';
	$checked_option_sem_p_lu_pm='';
	$checked_option_sem_p_ma_am='';
	$checked_option_sem_p_ma_pm='';
	$checked_option_sem_p_me_am='';
	$checked_option_sem_p_me_pm='';
	$checked_option_sem_p_je_am='';
	$checked_option_sem_p_je_pm='';
	$checked_option_sem_p_ve_am='';
	$checked_option_sem_p_ve_pm='';
	$checked_option_sem_p_sa_am='';
	$checked_option_sem_p_sa_pm='';
	$checked_option_sem_p_di_am='';
	$checked_option_sem_p_di_pm='';
	/*********************************************/

	// recup des données de la dernière table artt du user :
	$sql1 = 'SELECT * FROM conges_artt WHERE a_login="'. \includes\SQL::quote($login).'" AND a_date_fin_grille=\'9999-12-31\' '  ;
	$ReqLog1 = \includes\SQL::query($sql1);

	while ($resultat1 = $ReqLog1->fetch_array()) {
		if($resultat1['sem_imp_lu_am']=='Y') $checked_option_sem_imp_lu_am=' checked';
		if($resultat1['sem_imp_lu_pm']=='Y') $checked_option_sem_imp_lu_pm=' checked';
		if($resultat1['sem_imp_ma_am']=='Y') $checked_option_sem_imp_ma_am=' checked';
		if($resultat1['sem_imp_ma_pm']=='Y') $checked_option_sem_imp_ma_pm=' checked';
		if($resultat1['sem_imp_me_am']=='Y') $checked_option_sem_imp_me_am=' checked';
		if($resultat1['sem_imp_me_pm']=='Y') $checked_option_sem_imp_me_pm=' checked';
		if($resultat1['sem_imp_je_am']=='Y') $checked_option_sem_imp_je_am=' checked';
		if($resultat1['sem_imp_je_pm']=='Y') $checked_option_sem_imp_je_pm=' checked';
		if($resultat1['sem_imp_ve_am']=='Y') $checked_option_sem_imp_ve_am=' checked';
		if($resultat1['sem_imp_ve_pm']=='Y') $checked_option_sem_imp_ve_pm=' checked';
		if($resultat1['sem_imp_sa_am']=='Y') $checked_option_sem_imp_sa_am=' checked';
		if($resultat1['sem_imp_sa_pm']=='Y') $checked_option_sem_imp_sa_pm=' checked';
		if($resultat1['sem_imp_di_am']=='Y') $checked_option_sem_imp_di_am=' checked';
		if($resultat1['sem_imp_di_pm']=='Y') $checked_option_sem_imp_di_pm=' checked';

		if($resultat1['sem_p_lu_am']=='Y') $checked_option_sem_p_lu_am=' checked';
		if($resultat1['sem_p_lu_pm']=='Y') $checked_option_sem_p_lu_pm=' checked';
		if($resultat1['sem_p_ma_am']=='Y') $checked_option_sem_p_ma_am=' checked';
		if($resultat1['sem_p_ma_pm']=='Y') $checked_option_sem_p_ma_pm=' checked';
		if($resultat1['sem_p_me_am']=='Y') $checked_option_sem_p_me_am=' checked';
		if($resultat1['sem_p_me_pm']=='Y') $checked_option_sem_p_me_pm=' checked';
		if($resultat1['sem_p_je_am']=='Y') $checked_option_sem_p_je_am=' checked';
		if($resultat1['sem_p_je_pm']=='Y') $checked_option_sem_p_je_pm=' checked';
		if($resultat1['sem_p_ve_am']=='Y') $checked_option_sem_p_ve_am=' checked';
		if($resultat1['sem_p_ve_pm']=='Y') $checked_option_sem_p_ve_pm=' checked';
		if($resultat1['sem_p_sa_am']=='Y') $checked_option_sem_p_sa_am=' checked';
		if($resultat1['sem_p_sa_pm']=='Y') $checked_option_sem_p_sa_pm=' checked';
		if($resultat1['sem_p_di_am']=='Y') $checked_option_sem_p_di_am=' checked';
		if($resultat1['sem_p_di_pm']=='Y') $checked_option_sem_p_di_pm=' checked';
		$date_deb_grille=$resultat1['a_date_debut_grille'];
		$date_fin_grille=$resultat1['a_date_fin_grille'];
	}


	echo '<h4>'. _('admin_temps_partiel_titre') .' :</h4>';
	echo "<table class=\"table table-hover table-responsive table-condensed table-striped\">\n";
	echo '<tr>';
	echo '<td>';
		//tableau semaines impaires
		echo '<b><u>'. _('admin_temps_partiel_sem_impaires') .' :</u></b><br>';
		$tab_checkbox_sem_imp=array();
		$imp_lu_am='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_lu_am]" value="Y" '.$checked_option_sem_imp_lu_am.'>';
		$imp_lu_pm='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_lu_pm]" value="Y" '.$checked_option_sem_imp_lu_pm.'>';
		$imp_ma_am='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_ma_am]" value="Y" '.$checked_option_sem_imp_ma_am.'>';
		$imp_ma_pm='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_ma_pm]" value="Y" '.$checked_option_sem_imp_ma_pm.'>';
		$imp_me_am='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_me_am]" value="Y" '.$checked_option_sem_imp_me_am.'>';
		$imp_me_pm='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_me_pm]" value="Y" '.$checked_option_sem_imp_me_pm.'>';
		$imp_je_am='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_je_am]" value="Y" '.$checked_option_sem_imp_je_am.'>';
		$imp_je_pm='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_je_pm]" value="Y" '.$checked_option_sem_imp_je_pm.'>';
		$imp_ve_am='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_ve_am]" value="Y" '.$checked_option_sem_imp_ve_am.'>';
		$imp_ve_pm='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_ve_pm]" value="Y" '.$checked_option_sem_imp_ve_pm.'>';
		if($_SESSION['config']['samedi_travail'])
		{
			$imp_sa_am='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_sa_am]" value="Y" '.$checked_option_sem_imp_sa_am.'>';
			$imp_sa_pm='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_sa_pm]" value="Y" '.$checked_option_sem_imp_sa_pm.'>';
		}
		if($_SESSION['config']['dimanche_travail'])
		{
			$imp_di_am='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_di_am]" value="Y" '.$checked_option_sem_imp_di_am.'>';
			$imp_di_pm='<input type="checkbox" name="tab_checkbox_sem_imp[sem_imp_di_pm]" value="Y" '.$checked_option_sem_imp_di_pm.'>';
		}

		echo '<table cellpadding="1" class="tablo">';
		echo '<thead>';
		echo '<tr>';
			echo '<td></td>';
			echo '<td class="histo">'. _('lundi') .'</td>';
			echo '<td class="histo">'. _('mardi') .'</td>';
			echo '<td class="histo">'. _('mercredi') .'</td>';
			echo '<td class="histo">'. _('jeudi') .'</td>';
			echo '<td class="histo">'. _('vendredi') .'</td>';
			if($_SESSION['config']['samedi_travail'])
				echo '<td class="histo">'. _('samedi') .'</td>';
			if($_SESSION['config']['dimanche_travail'])
				echo '<td class="histo">'. _('dimanche') .'</td>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr align="center">';
			echo '<td class="histo">'. _('admin_temps_partiel_am') .'</td>';
			echo '<td class="histo">'.$imp_lu_am.'</td>';
			echo '<td class="histo">'.$imp_ma_am.'</td>';
			echo '<td class="histo">'.$imp_me_am.'</td>';
			echo '<td class="histo">'.$imp_je_am.'</td>';
			echo '<td class="histo">'.$imp_ve_am.'</td>';
			if($_SESSION['config']['samedi_travail'])
				echo '<td class="histo">'.$imp_sa_am.'</td>';
			if($_SESSION['config']['dimanche_travail'])
				echo '<td class="histo">'.$imp_di_am.'</td>';
		echo '</tr>';
		echo '<tr align="center">';
			echo '<td class="histo">'. _('admin_temps_partiel_pm') .'</td>';
			echo '<td class="histo">'.$imp_lu_pm.'</td>';
			echo '<td class="histo">'.$imp_ma_pm.'</td>';
			echo '<td class="histo">'.$imp_me_pm.'</td>';
			echo '<td class="histo">'.$imp_je_pm.'</td>';
			echo '<td class="histo">'.$imp_ve_pm.'</td>';
			if($_SESSION['config']['samedi_travail'])
				echo '<td class="histo">'.$imp_sa_pm.'</td>';
			if($_SESSION['config']['dimanche_travail'])
				echo '<td class="histo">'.$imp_di_pm.'</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';

	echo '</td>';
	echo ' <td><img src="'. TEMPLATE_PATH . 'img/shim.gif" width="15" height="2" border="0" vspace="0" hspace="0"></td>';
	echo ' <td>';

		//tableau semaines paires
		echo '<b><u>'. _('admin_temps_partiel_sem_paires') .':</u></b><br>';
		$tab_checkbox_sem_p=array();
		$p_lu_am='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_lu_am]" value="Y" '.$checked_option_sem_p_lu_am.'>';
		$p_lu_pm='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_lu_pm]" value="Y" '.$checked_option_sem_p_lu_pm.'>';
		$p_ma_am='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_ma_am]" value="Y" '.$checked_option_sem_p_ma_am.'>';
		$p_ma_pm='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_ma_pm]" value="Y" '.$checked_option_sem_p_ma_pm.'>';
		$p_me_am='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_me_am]" value="Y" '.$checked_option_sem_p_me_am.'>';
		$p_me_pm='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_me_pm]" value="Y" '.$checked_option_sem_p_me_pm.'>';
		$p_je_am='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_je_am]" value="Y" '.$checked_option_sem_p_je_am.'>';
		$p_je_pm='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_je_pm]" value="Y" '.$checked_option_sem_p_je_pm.'>';
		$p_ve_am='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_ve_am]" value="Y" '.$checked_option_sem_p_ve_am.'>';
		$p_ve_pm='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_ve_pm]" value="Y" '.$checked_option_sem_p_ve_pm.'>';
		$p_sa_am='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_sa_am]" value="Y" '.$checked_option_sem_p_sa_am.'>';
		$p_sa_pm='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_sa_pm]" value="Y" '.$checked_option_sem_p_sa_pm.'>';
		$p_di_am='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_di_am]" value="Y" '.$checked_option_sem_p_di_am.'>';
		$p_di_pm='<input type="checkbox" name="tab_checkbox_sem_p[sem_p_di_pm]" value="Y" '.$checked_option_sem_p_di_pm.'>';

		echo '<table cellpadding="1"  class="tablo">';
		echo '<thead>';
		echo '<tr>';
			echo '<td></td>';
			echo '<td class="histo">'. _('lundi') .'</td>';
			echo '<td class="histo">'. _('mardi') .'</td>';
			echo '<td class="histo">'. _('mercredi') .'</td>';
			echo '<td class="histo">'. _('jeudi') .'</td>';
			echo '<td class="histo">'. _('vendredi') .'</td>';
			if($_SESSION['config']['samedi_travail'])
				echo '<td class="histo">'. _('samedi') .'</td>';
			if($_SESSION['config']['dimanche_travail'])
				echo '<td class="histo">'. _('dimanche') .'</td>';
		echo '</tr>';
		echo '</thead>';
		echo '<tbody>';
		echo '<tr align="center">';
			echo '<td class="histo">'. _('admin_temps_partiel_am') .'</td>';
			echo '<td class="histo">'.$p_lu_am.'</td>';
			echo '<td class="histo">'.$p_ma_am.'</td>';
			echo '<td class="histo">'.$p_me_am.'</td>';
			echo '<td class="histo">'.$p_je_am.'</td>';
			echo '<td class="histo">'.$p_ve_am.'</td>';
			if($_SESSION['config']['samedi_travail'])
				echo '<td class="histo">'.$p_sa_am.'</td>';
			if($_SESSION['config']['dimanche_travail'])
				echo '<td class="histo">'.$p_di_am.'</td>';
		echo '</tr>';
		echo '<tr align="center">';
			echo '<td class="histo">'. _('admin_temps_partiel_pm') .'</td>';
			echo '<td class="histo">'.$p_lu_pm.'</td>';
			echo '<td class="histo">'.$p_ma_pm.'</td>';
			echo '<td class="histo">'.$p_me_pm.'</td>';
			echo '<td class="histo">'.$p_je_pm.'</td>';
			echo '<td class="histo">'.$p_ve_pm.'</td>';
			if($_SESSION['config']['samedi_travail'])
				echo '<td class="histo">'.$p_sa_pm.'</td>';
			if($_SESSION['config']['dimanche_travail'])
				echo '<td class="histo">'.$p_di_pm.'</td>';
		echo '</tr>';
		echo '</tbody>';
		echo '</table>';
	echo '</td>';
	echo '</tr>';
	echo '<tr>';
	echo '<td colspan="3" class="inline-date">';
		$jour_default=date('d');
		$mois_default=date('m');
		$year_default=date('Y');
		echo '<strong>' . _('admin_temps_partiel_date_valid') . "</strong> ";
		affiche_selection_new_jour($jour_default);  // la variable est $new_jour
		affiche_selection_new_mois($mois_default);  // la variable est $new_mois
		affiche_selection_new_year($year_default-2, $year_default+10, $year_default );  // la variable est $new_year
	echo '</td>';
	echo '</tr>';
	echo '</table>';

}


function commit_modif_user_groups($choix_user, &$checkbox_user_groups,  $DEBUG=FALSE)
{


	$result_insert=FALSE;
	// on supprime tous les anciens groupes du user, puis on ajoute tous ceux qui sont dans la tableau checkbox (si il n'est pas vide)
	$sql_del = 'DELETE FROM conges_groupe_users WHERE gu_login=\''. \includes\SQL::quote($choix_user).'\'';
	$ReqLog_del = \includes\SQL::query($sql_del);

	if( ($checkbox_user_groups!="") && (count ($checkbox_user_groups)!=0) )
	{
		foreach($checkbox_user_groups as $gid => $value)
		{
			$sql_insert = "INSERT INTO conges_groupe_users SET gu_gid=$gid, gu_login='$choix_user' "  ;
			$result_insert = \includes\SQL::query($sql_insert);
		}
	}
	else
		$result_insert=TRUE;

	return $result_insert;
}

