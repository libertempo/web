<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2005 (said Benaddi)

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

include INCLUDE_PATH .'fonction.php';
include ROOT_PATH .'fonctions_conges.php'; // for init_config_tab()
$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config

//on récupère le hash du user
$usrh = $_GET['usr'];

//on récupère le nom associé au hash
$session_username = unhash_user($usrh);

if ($session_username != "")
	export_ical($session_username);

// export des périodes des conges et d'absences comprise entre les 2 dates , dans un fichier texte au format ICAL
function export_ical($user_login, $DEBUG=FALSE)
{
	$good_date_debut = date("Y-m-d", strtotime("-1 year"));
	$good_date_fin = date("Y-m-d", strtotime('+1 year'));
		/********************************/
		// initialisation de variables communes a ttes les periodes

		// recup des infos du user
		$tab_infos_user=recup_infos_du_user($user_login, "",  $DEBUG);

		$tab_types_abs=recup_tableau_tout_types_abs($DEBUG) ;

		/********************************/
		// affichage dans un fichier non html !

		header("content-type: application/ics");
		header("Content-disposition: filename=php_conges.ics");


		echo "BEGIN:VCALENDAR\r\n" .
				"PRODID:-//Libertempo \r\n" .
				"VERSION:2.0\r\n\r\n";

		// SELECT des periodes à exporter .....
		// on prend toutes les periodes de conges qui chevauchent la periode donnée par les dates demandées
		$sql_periodes="SELECT p_date_deb, p_demi_jour_deb, p_date_fin, p_demi_jour_fin, p_commentaire, p_type, p_date_demande  " .
				'FROM conges_periode WHERE p_login=\''.SQL::quote($user_login).'\' AND p_etat=\'ok\' AND ((p_date_deb>=\''.SQL::quote($good_date_debut).'\' AND  p_date_deb<=\''.SQL::quote($good_date_fin).'\') OR (p_date_fin>=\''.SQL::quote($good_date_debut).'\' AND p_date_fin<=\''.SQL::quote($good_date_fin).'\'))';
		$res_periodes = SQL::query($sql_periodes);

		if($num_periodes=$res_periodes->num_rows!=0)
		{
			while ($result_periodes = $res_periodes->fetch_array())
			{
				$sql_date_debut=$result_periodes['p_date_deb'];
				$sql_demi_jour_deb=$result_periodes['p_demi_jour_deb'];
				$sql_date_fin=$result_periodes['p_date_fin'];
				$sql_demi_jour_fin=$result_periodes['p_demi_jour_fin'];
				$sql_type=$result_periodes['p_type'];
				$sql_etat=$result_periodes['p_etat'];
				$sql_dateh_demande=$result_periodes['p_date_demande'];

				// PB : les fichiers ical et vcal doivent être encodés en UTF-8, or php ne gère pas l'utf-8
				// on remplace donc les caractères spéciaux de la chaine de caractères
				$sql_comment=remplace_accents($result_periodes['p_commentaire']);

				// même problème
				$type_abs=remplace_accents($tab_types_abs[$sql_type]['libelle']) ;

				//conversion format date
				$replaceThis = Array('-' => '',':' => '',' ' => 'T',);
				$sql_date_dem=str_replace(array_keys($replaceThis), $replaceThis, $sql_dateh_demande);
				$DTSTAMP=$sql_date_dem."Z";
				$tab_date_deb=explode("-", $sql_date_debut);
				$tab_date_fin=explode("-", $sql_date_fin);

				//conversion etat demande en status
				switch ($sql_etat) {
					case "ok":
						$status="CONFIRMED";
						break;
					case "refus":
						$status="CANCELLED";
						break;
					default:
						$status="TENTATIVE";
					}
				if($sql_demi_jour_deb=="am")
					$DTSTART=$tab_date_deb[0].$tab_date_deb[1].$tab_date_deb[2]."T070000Z";   // .....
				else
					$DTSTART=$tab_date_deb[0].$tab_date_deb[1].$tab_date_deb[2]."T120000Z";   // .....

				if($sql_demi_jour_fin=="am")
					$DTEND=$tab_date_fin[0].$tab_date_fin[1].$tab_date_fin[2]."T120000Z";   // .....
				else
					$DTEND=$tab_date_fin[0].$tab_date_fin[1].$tab_date_fin[2]."T210000Z";   // .....

					echo "BEGIN:VEVENT\r\n" .
						"DTSTAMP:$DTSTAMP\r\n" .
						"ORGANIZER:MAILTO:".$tab_infos_user['email']."\r\n" .
						"CREATED:$DTSTART\r\n" .
						"STATUS:$status\r\n" .
						"UID:$user_login@Libertempo-$sql_date_dem\r\n";
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

function remplace_accents($str)
{
	$accent        = array("à", "â", "ä", "é", "è", "ê", "ë", "î", "ï", "ô", "ö", "ù", "û", "ü", "ç");
	$sans_accent   = array("a", "a", "a", "e", "e", "e", "e", "i", "i", "o", "o", "u", "u", "u", "c");
	return str_replace($accent, $sans_accent, $str) ;

}

?>
