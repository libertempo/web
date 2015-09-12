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

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.4.0 vers 1.4.1
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';
include'fonctions_install.php' ;

$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=FALSE;
//$DEBUG=TRUE;

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;

	// résumé des étapes :
	// 1 : mise à jour du champ login dans les tables (respect de la casse)

	include CONFIG_PATH .'dbconnect.php' ;

	if( !$DEBUG )
	{
		// on lance les etapes (fonctions) séquentiellement
		e1_insert_into_conges_config( $DEBUG);
		e2_drop_table_historique_ajout( $DEBUG);

		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=mise_a_jour.php?etape=4&version=$version&lang=$lang\">";
	}
	else
	{
		// on lance les etape (fonctions) séquentiellement :
		// avec un arret à la fin de chaque étape

		$sub_etape=( (isset($_GET['sub_etape'])) ? $_GET['sub_etape'] : ( (isset($_POST['sub_etape'])) ? $_POST['sub_etape'] : 0 ) ) ;

		if($sub_etape==0) { echo "<a href=\"$PHP_SELF?sub_etape=1&version=$version&lang=$lang\">start upgrade_from_v1.3.0</a><br>\n"; }
		if($sub_etape==1) { e1_insert_into_conges_config( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=2&version=$version&lang=$lang\">sub_etape 1  OK</a><br>\n"; }
		if($sub_etape==2) { e2_drop_table_historique_ajout( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=3&version=$version&lang=$lang\">sub_etape 2  OK</a><br>\n"; }

		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		if($sub_etape==3) { echo "<a href=\"mise_a_jour.php?etape=4&version=$version&lang=$lang\">upgrade_from_v1.4.0  OK</a><br>\n"; }
	}


/********************************************************************************************************/
/********************************************************************************************************/
/***   FONCTIONS   ***/
/********************************************************************************************************/



/*****************************************************************/
/***   ETAPE 1 : Ajout de paramètres dans  conges_config       ***/
/*****************************************************************/
function e1_insert_into_conges_config( $DEBUG=FALSE)
{

	$sql_insert_1="INSERT INTO `conges_config` VALUES ('fermeture_bgcolor', '#7B9DE6', '14_Presentation', 'hidden', 'config_comment_fermeture_bgcolor')";
	$result_insert_1 = \includes\SQL::query($sql_insert_1)  ;

	$sql_insert_2="INSERT INTO `conges_config` VALUES ('texte_page_login', '', '02_PAGE D\'AUTENTIFICATION', 'texte', 'config_comment_texte_page_login')";
	$result_insert_2 = \includes\SQL::query($sql_insert_2)  ;

	$sql_insert_3="INSERT INTO `conges_config` VALUES ('solde_toujours_positif', 'FALSE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_solde_toujours_positif')";
	$result_insert_3 = \includes\SQL::query($sql_insert_3)  ;

}

function e2_drop_table_historique_ajout( $DEBUG=FALSE)
{

	if( test_create_table( $DEBUG))
	{
		if( test_drop_table( $DEBUG))
		{
			$sql_drop_1="DROP TABLE `conges_historique_ajout`";
			$result_drop_1 = \includes\SQL::query($sql_drop_1)  ;
		}
	}
}









