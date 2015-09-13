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

define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include_once ROOT_PATH .'fonctions_conges.php';
include_once INCLUDE_PATH .'fonction.php';
include_once INCLUDE_PATH .'session.php';

$DEBUG=FALSE;
//$DEBUG=TRUE


	/*** initialisation des variables ***/
	$session=session_id();
	/************************************/

	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET	/ POST
	$action     = getpost_variable('action') ;
	$user_login = getpost_variable('user_login') ;
	$date_debut = getpost_variable('date_debut') ;
	$date_fin   = getpost_variable('date_fin') ;
	$choix_format  = getpost_variable('choix_format') ;
	/*************************************/


	//connexion mysql

	if($action=="export")
	{
		if($choix_format=="ical")
			export_ical($user_login, $date_debut, $date_fin,  $DEBUG);
		else
			export_vcal($user_login, $date_debut, $date_fin,  $DEBUG);

		$comment_log = "export ical/vcal ($date_debut -> $date_fin) ";
		log_action(0, "", $user_login, $comment_log,  $DEBUG);
	}
	else
		form_saisie($user_login, $date_debut, $date_fin, $DEBUG);



/*******************************************************************************/
/**********  FONCTIONS  ********************************************************/

function form_saisie($user, $date_debut, $date_fin, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	$date_today=date("d-m-Y");
	if($date_debut=="")
		$date_debut=$date_today;
	if($date_fin=="")
		$date_fin=$date_today;

	$huser = hash_user($user);

	header_popup();
	
	
	echo "<center>\n";
	echo "<h1>". _('export_cal_titre') ."</h1>\n";

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	// saisie des dates
	echo "<tr>\n";
		echo "<td align=\"center\">\n";
		echo "<b>". _('export_cal_from_date') ."</b> <input type=\"text\" name=\"date_debut\" size=\"10\" maxlength=\"10\" value=\"$date_debut\" style=\"background-color: #D4D4D4; \" readonly=\"readonly\"> \n";
		echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('fonctions_export.php?session=$session&champ_date=date_debut','calendardebut',250,220);\">\n";
		echo "<img src=\"". TEMPLATE_PATH . "img/1day.png\" border=\"0\" title=\"". _('export_cal_saisir_debut') ."\" alt=\"". _('export_cal_saisir_debut') ."\"></a>\n";
		echo "</td>\n";
		echo "<td align=\"center\">\n";
		echo "<b>". _('export_cal_to_date') ."</b> <input type=\"text\" name=\"date_fin\" size=\"10\" maxlength=\"10\" value=\"$date_fin\" style=\"background-color: #D4D4D4; \" readonly=\"readonly\"> \n";
		echo "<a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('fonctions_export.php?session=$session&champ_date=date_fin','calendarfin',250,220);\">\n";
		echo "<img src=\"". TEMPLATE_PATH . "img/1day.png\" border=\"0\" title=\"". _('export_cal_saisir_fin') ."\" alt=\"". _('export_cal_saisir_fin') ."\"></a>\n";
		echo "</td>\n";
	echo "</tr>\n";
	// ligne vide
	echo "<tr>\n";
		echo "<td colspan=\"2\">&nbsp;\n";
		echo "</td>\n";
	echo "</tr>\n";
	// saisie du format
	echo "<tr>\n";
	echo "<td colspan=\"2\">\n";
		echo "<table align=\"center\"><tr>\n";
		echo "<td><b>". _('export_cal_format') ."</b> : </td>\n";
		echo "<td align=\"left\"><b>ical</b><input type=\"radio\" name=\"choix_format\" value=\"ical\" checked> </td>\n";
		echo "<td align=\"right\"> <b>vcal</b><input type=\"radio\" name=\"choix_format\" value=\"vcal\"></td>\n";
		echo "</tr></table>\n";
	echo "</td>\n";
	echo "<tr>\n";
	echo "<td align=\"center\">&nbsp;</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"hidden\" name=\"action\" value=\"export\">\n";
	echo "	<input type=\"submit\" value=\"". _('form_submit') ."\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"button\" value=\"". _('form_close_window') ."\" onClick=\"javascript:window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	echo " <a href='".ROOT_PATH."export/ics_export.php?usr=".$huser."'>Export ical<a>";

	bottom();

}


// export des périodes des conges et d'absences comprise entre les 2 dates , dans un fichier texte au format ICAL
function export_ical($user_login, $date_debut, $date_fin,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	//inverse l'ordre de la date jj-mm-yyyy --> yyy-mm-jj
	$good_date_debut=inverse_date($date_debut, $DEBUG);
	$good_date_fin=inverse_date($date_fin, $DEBUG);

	if($good_date_debut > $good_date_fin)  // si $date_debut posterieure a $date_fin
		// redirige vers page de saisie
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?session=$session&date_debut=$date_debut&date_fin=$date_fin&choix_format=ical\">";
	else
	{
		/********************************/
		// initialisation de variables communes a ttes les periodes

		// recup des infos du user
		$tab_infos_user=recup_infos_du_user($_SESSION['userlogin'], "",  $DEBUG);

		$tab_types_abs=recup_tableau_tout_types_abs( $DEBUG) ;

		if(function_exists("date_default_timezone_get"))   // car date_default_timezone_get() n'existe que depuis PHP 5.1
			$DTSTAMP=date("Ymd").date_default_timezone_get();
		else
			$DTSTAMP=date("Ymd")."T142816Z";    // copier depuis un fichier ical

		/********************************/
		// affichage dans un fichier non html !

		header("content-type: application/ics");
		header("Content-disposition: filename=php_conges.ics");


		echo "BEGIN:VCALENDAR\r\n" .
				"PRODID:-//php_conges ".$_SESSION['config']['installed_version']."\r\n" .
				"VERSION:2.0\r\n\r\n";

		// SELECT des periodes à exporter .....
		// on prend toutes les periodes de conges qui chevauchent la periode donnée par les dates demandées
		$sql_periodes="SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_commentaire, p_type  " .
				'FROM conges_periode WHERE p_login="'. \includes\SQL::quote($_SESSION['userlogin']).'" AND p_etat=\'ok\' AND ((p_date_deb>="'. \includes\SQL::quote($good_date_debut).'" AND  p_date_deb<="'. \includes\SQL::quote($good_date_fin).'") OR (p_date_fin>="'. \includes\SQL::quote($good_date_debut).'" AND p_date_fin<="'. \includes\SQL::quote($good_date_fin).'"))';
		$res_periodes = \includes\SQL::query($sql_periodes);

		if($num_periodes=$res_periodes->num_rows!=0)
		{
			while ($result_periodes = $res_periodes->fetch_array())
			{
				$sql_date_debut=$result_periodes['p_date_deb'];
				$sql_demi_jour_deb=$result_periodes['p_demi_jour_deb'];
				$sql_date_fin=$result_periodes['p_date_fin'];
				$sql_demi_jour_fin=$result_periodes['p_demi_jour_fin'];
				$sql_type=$result_periodes['p_type'];

				// PB : les fichiers ical et vcal doivent être encodés en UTF-8, or php ne gère pas l'utf-8
				// on remplace donc les caractères spéciaux de la chaine de caractères
				$sql_comment=remplace_accents($result_periodes['p_commentaire']);

				// même problème
				$type_abs=remplace_accents($tab_types_abs[$sql_type]['libelle']) ;

				$tab_date_deb=explode("-", $sql_date_debut);
				$tab_date_fin=explode("-", $sql_date_fin);
				if($sql_demi_jour_deb=="am")
					$DTSTART=$tab_date_deb[0].$tab_date_deb[1].$tab_date_deb[2]."T000000Z";   // .....
				else
					$DTSTART=$tab_date_deb[0].$tab_date_deb[1].$tab_date_deb[2]."T120000Z";   // .....

				if($sql_demi_jour_fin=="am")
					$DTEND=$tab_date_fin[0].$tab_date_fin[1].$tab_date_fin[2]."T120000Z";   // .....
				else
					$DTEND=$tab_date_fin[0].$tab_date_fin[1].$tab_date_fin[2]."T235900Z";   // .....

					echo "BEGIN:VEVENT\r\n" .
						"DTSTAMP:$DTSTAMP\r\n" .
						"ORGANIZER;CN=".$_SESSION['userlogin'].":MAILTO:".$tab_infos_user['email']."\r\n" .
						"CREATED:$DTSTAMP\r\n" .
						"UID:php_conges\r\n" .
						"SEQUENCE:0\r\n" .
						"LAST-MODIFIED:$DTSTAMP\r\n";
				if($sql_comment!="")
					echo "DESCRIPTION:$sql_comment\r\n";
				echo "SUMMARY:$type_abs\r\n" .
						"CLASS:PUBLIC\r\n" .
						"PRIORITY:1\r\n" .
						"DTSTART:$DTSTART\r\n" .
						"DTEND:$DTEND\r\n" .
						"TRANSP:OPAQUE\r\n" .
						"END:VEVENT\r\n\r\n" ;
			}
		}

		echo "END:VCALENDAR\r\n";

	}
}


// export des périodes des conges et d'absences comprise entre les 2 dates , dans un fichier texte au format VCAL
function export_vcal($user_login, $date_debut, $date_fin,  $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	//inverse l'ordre de la date jj-mm-yyyy --> yyy-mm-jj
	$good_date_debut=inverse_date($date_debut, $DEBUG);
	$good_date_fin=inverse_date($date_fin, $DEBUG);

	if($good_date_debut > $good_date_fin)  // si $date_debut posterieure a $date_fin
		// redirige vers page de saisie
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=$PHP_SELF?session=$session&date_debut=$date_debut&date_fin=$date_fin&choix_format=ical\">";
	else
	{
		/********************************/
		// initialisation de variables communes a ttes les periodes

		// recup des infos du user
		$tab_infos_user=recup_infos_du_user($_SESSION['userlogin'], "",  $DEBUG);

		$tab_types_abs=recup_tableau_tout_types_abs( $DEBUG) ;

		if(function_exists("date_default_timezone_get"))   // car date_default_timezone_get() n'existe que depuis PHP 5.1
			$DTSTAMP=date("Ymd").date_default_timezone_get();
		else
			$DTSTAMP=date("Ymd")."T142816Z";    // copier depuis un fichier ical

		/********************************/
		// affichage dans un fichier non html !

		header("content-type: application/ics");
		header("Content-disposition: filename=php_conges.ics");


		echo "BEGIN:VCALENDAR\r\n" .
				"PRODID:-//php_conges ".$_SESSION['config']['installed_version']."\r\n" .
				"VERSION:1.0\r\n\r\n";

		// SELECT des periodes à exporter .....
		// on prend toutes les periodes de conges qui chevauchent la periode donnée par les dates demandées
		$sql_periodes="SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_commentaire, p_type  " .
				'FROM conges_periode WHERE p_login="'. \includes\SQL::quote($_SESSION['userlogin']).'" AND p_etat=\'ok\' AND (p_date_deb>="'.\includes\SQL::quote($good_date_debut).'" AND  p_date_deb<="'. \includes\SQL::quote($good_date_fin).'") OR (p_date_fin>="'. \includes\SQL::quote($good_date_debut).'" AND p_date_fin<="'.\includes\SQL::quote($good_date_fin).'")';
		$res_periodes = \includes\SQL::query($sql_periodes);

		if($num_periodes=$res_periodes->num_rows!=0)
		{
			while ($result_periodes = $res_periodes->fetch_array())
			{
				$sql_date_debut=$result_periodes['p_date_deb'];
				$sql_demi_jour_deb=$result_periodes['p_demi_jour_deb'];
				$sql_date_fin=$result_periodes['p_date_fin'];
				$sql_demi_jour_fin=$result_periodes['p_demi_jour_fin'];
				$sql_type=$result_periodes['p_type'];

				// PB : les fichiers ical et vcal doivent être encodés en UTF-8, or php ne gère pas l'utf-8
				// on remplace donc les caractères spéciaux de la chaine de caractères
				$sql_comment=remplace_accents($result_periodes['p_commentaire']);

				// même problème
				$type_abs=remplace_accents($tab_types_abs[$sql_type]['libelle']) ;

				$tab_date_deb=explode("-", $sql_date_debut);
				$tab_date_fin=explode("-", $sql_date_fin);
				if($sql_demi_jour_deb=="am")
					$DTSTART=$tab_date_deb[0].$tab_date_deb[1].$tab_date_deb[2]."T000000Z";   // .....
				else
					$DTSTART=$tab_date_deb[0].$tab_date_deb[1].$tab_date_deb[2]."T120000Z";   // .....

				if($sql_demi_jour_fin=="am")
					$DTEND=$tab_date_fin[0].$tab_date_fin[1].$tab_date_fin[2]."T120000Z";   // .....
				else
					$DTEND=$tab_date_fin[0].$tab_date_fin[1].$tab_date_fin[2]."T235900Z";   // .....

				echo "BEGIN:VEVENT\r\n" .
						"DTSTART:$DTSTART\r\n" .
						"DTEND:$DTEND\r\n" .
						"CREATED:$DTSTAMP\r\n" .
						"UID:php_conges\r\n" .
						"SEQUENCE:1\r\n" .
						"LAST-MODIFIED:$DTSTAMP\r\n" .
						"X-ORGANIZER;MAILTO:".$tab_infos_user['email']."\r\n";
				if($sql_comment!="")
					echo "DESCRIPTION:$sql_comment\r\n";
				echo "SUMMARY:$type_abs\r\n" .
						"CLASS:PUBLIC\r\n" .
						"PRIORITY:1\r\n" .
						"TRANSP:0\r\n" .
						"END:VEVENT\r\n\r\n" ;
			}
		}

		echo "END:VCALENDAR\r\n";

	}
}


/*******************************************************************************/
/**********  FONCTIONS  DE GENERATION VCAL ET ICAL *****************************/


//inverse l'ordre de la date jj-mm-yyyy --> yyy-mm-jj
function inverse_date($date, $DEBUG=FALSE)
{
	$tab=explode("-", $date);
	$reverse_date=$tab[2]."-".$tab[1]."-".$tab[0] ;

	if( $DEBUG ) { echo "reverse_date : $date -> $reverse_date<br>\n" ; }

	return $reverse_date;
}


// remplace le caractere accentué ou transformé, par le caractere normal !
function remplace_accents($str)
{
	$accent        = array("à", "â", "ä", "é", "è", "ê", "ë", "î", "ï", "ô", "ö", "ù", "û", "ü", "ç");
	$sans_accent   = array("a", "a", "a", "e", "e", "e", "e", "i", "i", "o", "o", "u", "u", "u", "c");
	return str_replace($accent, $sans_accent, $str) ;

}



