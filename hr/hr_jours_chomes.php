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

if (file_exists(CONFIG_PATH .'config_ldap.php'))
	include CONFIG_PATH .'config_ldap.php';

$DEBUG=FALSE;
//$DEBUG=TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_hr", $DEBUG);


	/*** initialisation des variables ***/
	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$choix_action 				= getpost_variable('choix_action');
	$year_calendrier_saisie		= getpost_variable('year_calendrier_saisie', 0);
	$tab_checkbox_j_chome		= getpost_variable('tab_checkbox_j_chome');
	/*************************************/

	if( $DEBUG ) { echo "choix_action = $choix_action # year_calendrier_saisie = $year_calendrier_saisie<br>\n"; print_r($year_calendrier_saisie) ; echo "<br>\n"; }

	// si l'année n'est pas renseignée, on prend celle du jour
	if($year_calendrier_saisie==0)
		$year_calendrier_saisie = date("Y");

	$add_css = '<style>#onglet_menu .onglet{ width: 50% ;}</style>';
	
//	header_menu('hr', NULL, $add_css);

	echo "<div class=\"pager\">\n";
//		echo "<div class=\"onglet active\">" . _('admin_jours_chomes_titre') . " <span class=\"current-year\">$year_calendrier_saisie</span></div>";
		echo "<div class=\"onglet calendar-nav\">\n";
			// navigation 
			$prev_link = "$PHP_SELF?session=$session&onglet=jours_chomes&year_calendrier_saisie=". ($year_calendrier_saisie - 1);
			$next_link = "$PHP_SELF?session=$session&onglet=jours_chomes&year_calendrier_saisie=". ($year_calendrier_saisie + 1);
			echo "<ul>\n";
			echo "<li><a href=\"$prev_link\" class=\"calendar-prev\"><i class=\"fa fa-chevron-left\"></i><span>année précédente</span></a></li>\n";
			echo "<li class=\"current-year\">$year_calendrier_saisie</li>\n";
			echo "<li><a href=\"$next_link\" class=\"calendar-next\"><i class=\"fa fa-chevron-right\"></i><span>année suivante</span></a></li>\n";
			echo "</ul>\n";
		echo "</div>\n";
	echo "</div>\n";
	if($choix_action=="commit")
		commit_saisie($tab_checkbox_j_chome, $DEBUG);
	echo "<div class=\"wrapper\">\n";
		saisie($year_calendrier_saisie, $DEBUG);
	echo "</div>\n";

	bottom();


/***************************************************************/
/**********  FONCTIONS  ****************************************/

function saisie($year_calendrier_saisie, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	// si l'année n'est pas renseignée, on prend celle du jour
	if($year_calendrier_saisie==0)
		$year_calendrier_saisie = date("Y");

	// on construit le tableau des jours feries de l'année considérée
	$tab_year=array();
	get_tableau_jour_feries($year_calendrier_saisie, $tab_year,$DEBUG);
	if( $DEBUG ) { echo "tab_year = "; print_r($tab_year); echo "<br>\n"; }

	//calcul automatique des jours feries
	if($_SESSION['config']['calcul_auto_jours_feries_france'])
	{
		$tableau_jour_feries=fcListJourFeries($year_calendrier_saisie) ;
		if( $DEBUG ) { echo "tableau_jour_feries = "; print_r($tableau_jour_feries); echo "<br>\n"; }
		foreach ($tableau_jour_feries as $i => $value) 
		{
			if(!in_array ("$value", $tab_year))
				$tab_year[]=$value;
		}
	}
	if( $DEBUG ) { echo "tab_year = "; print_r($tab_year); echo "<br>\n"; }

//	echo '<a href="' . ROOT_PATH . "hr/hr_index.php?session=$session\" class=\"admin-back\"><i class=\"fa fa-arrow-circle-o-left\"></i>Retour mode RH</a>\n";
	echo "<form action=\"$PHP_SELF?session=$session&onglet=jours_chomes&year_calendrier_saisie=$year_calendrier_saisie\" method=\"POST\">\n" ;
	echo "<div class=\"calendar\">\n";
	$months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

	foreach ($months as $month) {
		echo "<div class=\"month\">\n";
		echo "<div class=\"wrapper\">\n";
		echo affiche_calendrier_saisie_jours_chomes($year_calendrier_saisie, $month, $tab_year);
		echo "</div>\n";
		echo "</div>";
	}
	echo "</div>";
	echo "<div class=\"actions\">";
	echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit\">\n";
	echo "<input class=\"btn\" type=\"submit\" value=\"". _('form_submit') ."\">  \n";
	echo "</div>";
	echo "</form>\n" ;
}



// affichage du calendrier du mois avec les case à cocher
// on lui passe en parametre le tableau des jour chomé de l'année (pour pré-cocher certaines cases)
function  affiche_calendrier_saisie_jours_chomes($year, $mois, $tab_year, $DEBUG=FALSE)
{
	$jour_today=date("j");
	$jour_today_name=date("D");

	$first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
	$mois_name=date_fr("F", $first_jour_mois_timestamp);
	$first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	if($first_jour_mois_rang==0)
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)
		

	echo "<table>\n";
	/* affichage  2 premieres lignes */
	echo "<thead>\n";
	echo "	<tr align=\"center\" bgcolor=\"".$_SESSION['config']['light_grey_bgcolor']."\"><th colspan=7 class=\"titre\"> $mois_name $year </th></tr>\n" ;
	echo "	<tr>\n";
	echo "		<th class=\"cal-saisie2\">". _('lundi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('mardi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('mercredi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('jeudi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2\">". _('vendredi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2 weekend\">". _('samedi_1c') ."</th>\n";
	echo "		<th class=\"cal-saisie2 weekend\">". _('dimanche_1c') ."</th>\n";
	echo "	</tr>\n" ;
	echo "</thead>\n";

	/* affichage ligne 1 du mois*/
	echo "<tr>\n";
	// affichage des cellules vides jusqu'au 1 du mois ...
	for($i=1; $i<$first_jour_mois_rang; $i++) {
		echo affiche_jour_hors_mois ($mois,$i,$year,$tab_year);
	}
	// affichage des cellules cochables du 1 du mois à la fin de la ligne ...
	for($i=$first_jour_mois_rang; $i<8; $i++)
	{
		$j=$i-$first_jour_mois_rang+1;
		echo affiche_jour_checkbox ($mois,$j,$year,$tab_year);
	}
	echo "</tr>\n";

	/* affichage ligne 2 du mois*/
	echo "<tr>\n";
	for($i=8-$first_jour_mois_rang+1; $i<15-$first_jour_mois_rang+1; $i++) {
		echo affiche_jour_checkbox ($mois,$i,$year,$tab_year);
	}
	echo "</tr>\n";

	/* affichage ligne 3 du mois*/
	echo "<tr>\n";
	for($i=15-$first_jour_mois_rang+1; $i<22-$first_jour_mois_rang+1; $i++){
		echo affiche_jour_checkbox ($mois,$i,$year,$tab_year);
	}
	echo "</tr>\n";

	/* affichage ligne 4 du mois*/
	echo "<tr>\n";
	for($i=22-$first_jour_mois_rang+1; $i<29-$first_jour_mois_rang+1; $i++) {
		echo affiche_jour_checkbox ($mois,$i,$year,$tab_year);
	}
	echo "</tr>\n";

	/* affichage ligne 5 du mois (peut etre la derniere ligne) */
	echo "<tr>\n";
	for($i=29-$first_jour_mois_rang+1; $i<36-$first_jour_mois_rang+1 && checkdate($mois, $i, $year); $i++) {
		echo affiche_jour_checkbox ($mois,$i,$year,$tab_year);
	}
	
	for($i; $i<36-$first_jour_mois_rang+1; $i++) {
		echo affiche_jour_hors_mois ($mois,$i,$year,$tab_year);
	}
	echo "</tr>\n";

	/* affichage ligne 6 du mois (derniere ligne)*/
	echo "<tr>\n";
	for($i=36-$first_jour_mois_rang+1; checkdate($mois, $i, $year); $i++) {
		echo affiche_jour_checkbox ($mois,$i,$year,$tab_year);
	}

	for($i; $i<43-$first_jour_mois_rang+1; $i++) {
		echo affiche_jour_hors_mois ($mois,$i,$year,$tab_year);
	}
	echo "</tr>\n";

	echo "</table>\n";
}

function affiche_jour_checkbox ($mois,$i,$year,$tab_year) {
	$j_timestamp=mktime (0,0,0,$mois,$i,$year);
	$j_date=date("Y-m-d", $j_timestamp);
	$j_day=date("d", $j_timestamp);
	$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);
	$checked = in_array ("$j_date", $tab_year);
	
	return "<td  class=\"cal-saisie $td_second_class" . (($checked) ? ' fermeture' : '') . "\">$j_day<input type=\"checkbox\" name=\"tab_checkbox_j_chome[$j_date]\" value=\"Y\"" . (($checked) ? ' checked' : '') . "></td>\n";
}

function affiche_jour_hors_mois ($mois,$i,$year,$tab_year) {
	$j_timestamp=mktime (0,0,0,$mois,$i,$year);
	$td_second_class=get_td_class_of_the_day_in_the_week($j_timestamp);
	return "<td class=\"cal-saisie2 month-out $td_second_class\">&nbsp;</td>\n";
}


function confirm_saisie($tab_checkbox_j_chome, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	header_popup();	
	
	echo "<h1>". _('admin_jours_chomes_titre') ."</h1>\n";
	echo "<form action=\"$PHP_SELF?session=$session&onglet=jours_chomes\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr>\n";
	echo "<td align=\"center\">\n";

		foreach($tab_checkbox_j_chome as $key => $value)
		{
			$date_affiche=eng_date_to_fr($key);
			echo "$date_affiche<br>\n";
			echo "<input type=\"hidden\" name=\"tab_checkbox_j_chome[$key]\" value=\"$value\">\n";
		}
		echo "<input type=\"hidden\" name=\"choix_action\" value=\"commit\">\n";
		echo "<input type=\"submit\" value=\"". _('admin_jours_chomes_confirm') ."\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\">\n";
	echo "	<input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";

	bottom();

}

function commit_saisie($tab_checkbox_j_chome,$DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	if( $DEBUG ) { echo "tab_checkbox_j_chome : <br>\n"; print_r($tab_checkbox_j_chome); echo "<br>\n"; }

	// si l'année est déja renseignée dans la database, on efface ttes les dates de l'année
	if(verif_year_deja_saisie($tab_checkbox_j_chome, $DEBUG))
		$result=delete_year($tab_checkbox_j_chome,  $DEBUG);


	// on insert les nouvelles dates saisies
	$result=insert_year($tab_checkbox_j_chome, $DEBUG);
	
	// on recharge les jours feries dans les variables de session
	init_tab_jours_feries($DEBUG);

	if($result)
		echo "<div class=\"alert alert-success\">" . _('form_modif_ok') . "</div>\n";
	else
		echo "<div class=\"alert alert-danger\">". _('form_modif_not_ok') . "</div>\n";

	$date_1=key($tab_checkbox_j_chome);
	$tab_date = explode('-', $date_1);
	$comment_log = "saisie des jours chomés pour ".$tab_date[0] ;
	log_action(0, "", "", $comment_log, $DEBUG);
}


function insert_year($tab_checkbox_j_chome, $DEBUG=FALSE) {
	foreach($tab_checkbox_j_chome as $key => $value)
		$result = SQL::query('INSERT INTO conges_jours_feries SET jf_date=\''.SQL::quote($key).'\';');
	return true;
}

function delete_year($tab_checkbox_j_chome, $DEBUG=FALSE) {
	$date_1=key($tab_checkbox_j_chome);
	$year=substr($date_1, 0, 4);
	$sql_delete='DELETE FROM conges_jours_feries WHERE jf_date LIKE \''.SQL::quote($year).'%\' ;';
	$result = SQL::query($sql_delete);

	return true;
}

function verif_year_deja_saisie($tab_checkbox_j_chome, $DEBUG=FALSE) {
	$date_1=key($tab_checkbox_j_chome);
	$year=substr($date_1, 0, 4);
	$sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date LIKE \''.SQL::quote($year).'%\' ;';
	$relog = SQL::query($sql_select);
	return($relog->num_rows != 0);
}


// retourne un tableau des jours feriés de l'année dans un tables passé par référence
function get_tableau_jour_feries($year, &$tab_year,  $DEBUG=FALSE)
{

	$sql_select='SELECT jf_date FROM conges_jours_feries WHERE jf_date LIKE \''.SQL::quote($year).'-%\' ;';
	$res_select = SQL::query($sql_select);
	$num_select = $res_select->num_rows;

	if($num_select!=0)
	{
		while($result_select = $res_select->fetch_array())
		{
			$tab_year[]=$result_select["jf_date"];
		}
	}


}

//fonction de recherche des jours fériés de l'année demandée
// trouvée sur http://www.phpcs.com/codes/LISTE-JOURS-FERIES-ANNEE_32791.aspx
function fcListJourFeries($iAnnee = 2000) 
{

	//Initialisation de variables
	$iCstJour = 3600*24;
	$tbJourFerie=array();
	
	// Détermination des dates toujours fixes
	$tbJourFerie["Jour de l an"]     = $iAnnee . "-01-01";
	$tbJourFerie["Armistice 39-45"]  = $iAnnee . "-05-08";
	$tbJourFerie["Toussaint"]        = $iAnnee . "-11-01";
	$tbJourFerie["Armistice 14-18"]  = $iAnnee . "-11-11";
	$tbJourFerie["Assomption"]       = $iAnnee . "-08-15";
	$tbJourFerie["Fete du travail"]  = $iAnnee . "-05-01";
	$tbJourFerie["Fete nationale"]   = $iAnnee . "-07-14";
	$tbJourFerie["Noel"]    = $iAnnee . "-12-25";
	
	// Récupération des fêtes mobiles
	     $tbJourFerie["Lundi de Paques"]   = $iAnnee . date( "-m-d", easter_date($iAnnee) + 1*$iCstJour );
	     $tbJourFerie["Jeudi de l ascenscion"] = $iAnnee . date( "-m-d", easter_date($iAnnee) + 39*$iCstJour );
		 
	// Retour du tableau des jours fériés pour l'année demandée
	return $tbJourFerie;
}

