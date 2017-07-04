<?php

define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';

include_once ROOT_PATH .'fonctions_conges.php';
include_once INCLUDE_PATH .'fonction.php';
include_once INCLUDE_PATH .'session.php';

	/*** initialisation des variables ***/
	/************************************/

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
	// GET	/ POST
	$action     = getpost_variable('action') ;
	$new_mois = getpost_variable('new_mois', date("m")) ;
	$new_year = getpost_variable('new_year', date("Y")) ;
	/*************************************/


	form_saisie($action, $new_mois, $new_year);


/*******************************************************************************/
/**********  FONCTIONS  ********************************************************/

function form_saisie($action, $new_mois, $new_year)
{
	$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

	header_popup();

    if($action=="imprim") {
        echo '<script type="text/javascript">OpenPopUp(\'calendrier.php?printable=1&mois=' . $mois . '&year=' . $year . ',\'calendrier\',1000,600);</script>';
    }


	echo "<center>\n";
	echo "<h3>". _('imprim_calendrier_titre') ."</h3>\n";

	echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
	echo "<table>\n";
	// choix du mois et annee
	echo "<tr>\n";
		echo "<td align=\"center\">\n";
		echo "<b>". _('divers_mois') ." : </b>\n";
		$mois_default=date("m");
		affiche_selection_new_mois($mois_default);  // la variable est $new_mois
		echo "</td>\n";
		echo "<td align=\"center\">\n";
		echo "<b>". _('divers_annee') ." : </b>\n";
		$year_default=date("Y");
		affiche_selection_new_year($year_default-5, $year_default+5, $year_default );  // la variable est $new_year
		echo "</td>\n";
	echo "</tr>\n";
	// ligne vide
	echo "<tr>\n";
		echo "<td colspan=\"2\">&nbsp;\n";
		echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"hidden\" name=\"action\" value=\"imprim\">\n";
	echo "	<input type=\"submit\" value=\"". _('form_submit') ."\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"button\" value=\"". _('form_close_window') ."\" onClick=\"window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";

	bottom();

}
