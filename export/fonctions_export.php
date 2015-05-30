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

define('_PHP_CONGES', 1);
define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include ROOT_PATH .'fonctions_conges.php';
include INCLUDE_PATH .'fonction.php';
include INCLUDE_PATH .'session.php';

$DEBUG=FALSE;
//$DEBUG=TRUE ;


	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$HTTP_REFERER=$_SERVER['HTTP_REFERER'] ;
	// GET / POST
	$year          = getpost_variable('year', date("Y")) ;
	$mois          = getpost_variable('mois', date("n")) ;
	$champ_date    = getpost_variable('champ_date') ;
	

	/*************************************/
	
// ATTENTION ne pas mettre cet appel avant les include car plantage sous windows !!!


$script = '<script language="javascript">
function envoi_date(valeur)
{
	window.opener.document.forms[0].'.$champ_date.'.value=valeur; window.close()
}
</script>';
	header_popup('calendar',$script);


					
	$jour_today=date("j");
	
	$mois_timestamp = mktime (0,0,0,$mois,1,$year);
	$nom_mois=date_fr("F", $mois_timestamp);
	
	// AFFICHAGE PAGE
	echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"100%\">\n";
	echo "<tr>\n";
	echo "   <td align=\"center\">\n";
	echo "   <h3>$nom_mois  $year</h3>\n";
	echo "   </td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "   <td align=\"center\">\n";
		
	// AFFICHAGE  TABLEAU (CALENDRIER)
	affiche_calendar($year, $mois, $DEBUG);
		
	echo "   </td>\n";
	echo "</tr>\n";
	
	echo "<tr>\n";
	echo "   <td align=\"center\">\n";
	/**********************/
	/* Boutons de defilement */
	affichage_boutons_defilement_calendar($mois, $year, $champ_date, $DEBUG) ;

	echo "   </td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	
/*******************************************************************************/
/**********  FONCTIONS  ********************************************************/

	
/******************************/
/* Boutons de defilement */
/******************************/
function affichage_boutons_defilement_calendar($mois, $year, $champ_date, $DEBUG=FALSE) 
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

		if($mois==12) $next_mois=1;  else $next_mois=$mois+1 ;
		if($mois==1) $prev_mois=12;  else $prev_mois=$mois-1 ;
		
		if($prev_mois==12) $prev_year=$year-1; else $prev_year=$year;
		if($next_mois==1) $next_year=$year+1; else $next_year=$year;

		echo "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\" width=\"90%\" >\n";
		echo "<tr>\n";
		echo "<td align=\"left\">
				<a href=\"$PHP_SELF?session=$session&mois=$prev_mois&year=$prev_year&champ_date=$champ_date\" method=\"POST\"> << ". _('divers_mois_precedent_maj_1') ." </a>
			</td>\n";
		echo "<td align=\"right\">
				<a href=\"$PHP_SELF?session=$session&mois=$next_mois&year=$next_year&champ_date=$champ_date\" method=\"POST\"> ". _('divers_mois_suivant_maj_1') ." >> </a>
			</td>\n";
		echo "</tr></table>\n";

}



// AFFICHAGE  TABLEAU (CALENDRIER)
function affiche_calendar($year, $mois, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	$jour_today      =date("j");
	$jour_today_name =date("D");
	$today_timestamp =mktime (0,0,0,date("m"),date("j"),date("Y"));
	
	$first_jour_mois_timestamp=mktime (0,0,0,$mois,1,$year);
	$mois_name=date_fr("F", $first_jour_mois_timestamp);
	$first_jour_mois_rang=date("w", $first_jour_mois_timestamp);      // jour de la semaine en chiffre (0=dim , 6=sam)
	if($first_jour_mois_rang==0)
		$first_jour_mois_rang=7 ;    // jour de la semaine en chiffre (1=lun , 7=dim)
		
	if($mois<10)
		$mois_value="0$mois";
	else
		$mois_value="$mois";
		
	// mise en gras du jour d'aujourd'hui
	
	

	// TABLEAU
	
	echo "<table cellpadding=\"1\" class=\"tablo-cal\" width=\"90%\">\n";
	/* affichage ligne des jours de la semaine*/
	echo "<tr>\n";
	echo "<td class=\"calendar-header\">". _('lundi_2c') ."</td>";
	echo "<td class=\"calendar-header\">". _('mardi_2c') ."</td>";
	echo "<td class=\"calendar-header\">". _('mercredi_2c') ."</td>";
	echo "<td class=\"calendar-header\">". _('jeudi_2c') ."</td>";
	echo "<td class=\"calendar-header\">". _('vendredi_2c') ."</td>";
	echo "<td class=\"calendar-header\">". _('samedi_2c') ."</td>";
	echo "<td class=\"calendar-header\">". _('dimanche_2c') ."</td>";
	echo "</tr>\n";

	/* affichage ligne 1 du mois*/
	echo "<tr>\n";
	// affichage des cellules vides jusqu'au 1 du mois ...
	for($i=1; $i<$first_jour_mois_rang; $i++) 
	{
		if( ($i==6) || ($i==7) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"calendar\">-</td>";
	}
	// affichage des cellules cochables du 1 du mois à la fin de la ligne ...
	for($i=$first_jour_mois_rang; $i<8; $i++) 
	{
		$j=$i-$first_jour_mois_rang+1 ;
		$jour="0$j";
		if( ($i==6) || ($i==7) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
			
		// affichage du jour d'aujourd'hui en gras
		$current_day_timestamp =mktime (0,0,0,$mois,$jour,$year);
		if($today_timestamp==$current_day_timestamp)
			$text="<b>$j</b>";
		else
			$text=$j;

		echo "<td bgcolor=$bgcolor class=\"calendar\"><a href=\"\" onClick=\"javascript:envoi_date('$jour-$mois_value-$year');\" class=\"calendar\">$text</a></td>";
	}
	echo "</tr>\n";
	
	/* affichage ligne 2 du mois*/
	echo "<tr>\n";
	for($i=8; $i<15; $i++) 
	{
		$j=$i-$first_jour_mois_rang+1;
		if($j<10)
			$jour="0$j";
		else
			$jour=$j;
		if( ($i==13) || ($i==14) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
			
		// affichage du jour d'aujourd'hui en gras
		$current_day_timestamp =mktime (0,0,0,$mois,$jour,$year);
		if($today_timestamp==$current_day_timestamp)
			$text="<b>$j</b>";
		else
			$text=$j;

		echo "<td bgcolor=$bgcolor class=\"calendar\"><a href=\"\" onClick=\"javascript:envoi_date('$jour-$mois_value-$year');\" class=\"calendar\">$text</a></td>";			
		
	}
	echo "</tr>\n";
	
	/* affichage ligne 3 du mois*/
	echo "<tr>\n";
	for($i=15; $i<22; $i++) 
	{
		$j=$i-$first_jour_mois_rang+1;
		if( ($i==20) || ($i==21) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
			
		// affichage du jour d'aujourd'hui en gras
		$current_day_timestamp =mktime (0,0,0,$mois,$j,$year);
		if($today_timestamp==$current_day_timestamp)
			$text="<b>$j</b>";
		else
			$text=$j;

		echo "<td bgcolor=$bgcolor class=\"calendar\"><a href=\"\" onClick=\"javascript:envoi_date('$j-$mois_value-$year');\" class=\"calendar\">$text</a></td>";
		
	}
	echo "</tr>\n";
	
	/* affichage ligne 4 du mois*/
	echo "<tr>\n";
	for($i=22; $i<29; $i++) {
		$j=$i-$first_jour_mois_rang+1;
		if( ($i==27) || ($i==28) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
			
		// affichage du jour d'aujourd'hui en gras
		$current_day_timestamp =mktime (0,0,0,$mois,$j,$year);
		if($today_timestamp==$current_day_timestamp)
			$text="<b>$j</b>";
		else
			$text=$j;

		echo "<td bgcolor=$bgcolor class=\"calendar\"><a href=\"\" onClick=\"javascript:envoi_date('$j-$mois_value-$year');\" class=\"calendar\">$text</a></td>";

	}
	echo "</tr>\n";
	
	/* affichage ligne 5 du mois (peut etre la derniere ligne) */
	echo "<tr>\n";
	for($i=29; ($i<36 && checkdate($mois, $i-$first_jour_mois_rang+1, $year)); $i++) 
	{
		$j=$i-$first_jour_mois_rang+1;
		if( ($i==34) || ($i==35) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
			
		// affichage du jour d'aujourd'hui en gras
		$current_day_timestamp =mktime (0,0,0,$mois,$j,$year);
		if($today_timestamp==$current_day_timestamp)
			$text="<b>$j</b>";
		else
			$text=$j;

		echo "<td bgcolor=$bgcolor class=\"calendar\"><a href=\"\" onClick=\"javascript:envoi_date('$j-$mois_value-$year');\" class=\"calendar\">$text</a></td>";
	}
	for($i=$j+$first_jour_mois_rang; $i<36; $i++) {
		if( ($i==34) || ($i==35) )
			$bgcolor=$_SESSION['config']['week_end_bgcolor'];
		else
			$bgcolor=$_SESSION['config']['semaine_bgcolor'];
		echo "<td bgcolor=$bgcolor class=\"calendar\">-</td>";	
	}
	echo "</tr>\n";
	
	if(checkdate($mois, 36-$first_jour_mois_rang+1, $year))
	{
		/* affichage ligne 6 du mois (derniere ligne)*/
		echo "<tr>\n";
		for($i=36; checkdate($mois, $i-$first_jour_mois_rang+1, $year); $i++) 
		{
			$j=$i-$first_jour_mois_rang+1;
			if( ($i==41) || ($i==42) )
				$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			else
				$bgcolor=$_SESSION['config']['semaine_bgcolor'];
			
		// affichage du jour d'aujourd'hui en gras
		$current_day_timestamp =mktime (0,0,0,$mois,$j,$year);
		if($today_timestamp==$current_day_timestamp)
			$text="<b>$j</b>";
		else
			$text=$j;

			echo "<td bgcolor=$bgcolor class=\"calendar\"><a href=\"\" onClick=\"javascript:envoi_date('$j-$mois_value-$year');\" class=\"calendar\">$text</a></td>";
		}
		for($i=$j+$first_jour_mois_rang; $i<43; $i++) 
		{
			if( ($i==41) || ($i==42) )
				$bgcolor=$_SESSION['config']['week_end_bgcolor'];
			else
				$bgcolor=$_SESSION['config']['semaine_bgcolor'];
			echo "<td bgcolor=$bgcolor class=\"calendar\">-</td>";	
		}
		echo "</tr>\n";
	}

	echo "</table>\n";
}

bottom();
