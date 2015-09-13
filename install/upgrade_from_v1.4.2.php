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

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.4.2 vers 1.5.0
/*******************************************************************/
include_once ROOT_PATH .'fonctions_conges.php' ;
include_once INCLUDE_PATH .'fonction.php';

$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=FALSE;
//$DEBUG=TRUE;

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;

	// résumé des étapes :
	// 1 : mise à jour du champ login dans les tables (respect de la casse)

	include_once CONFIG_PATH .'dbconnect.php' ;

	if( !$DEBUG )
	{
		// on lance les etapes (fonctions) séquentiellement
		\install\Fonctions::e1_create_table_conges_appli( $DEBUG);
		\install\Fonctions::e2_insert_into_conges_appli( $DEBUG);
		\install\Fonctions::e3_delete_from_table_conges_config( $DEBUG);
		\install\Fonctions::e4_alter_table_conges_users( $DEBUG);
		\install\Fonctions::e5_insert_into_conges_config( $DEBUG);
		\install\Fonctions::e6_alter_table_conges_solde_user( $DEBUG);
		\install\Fonctions::e7_alter_tables_taille_login( $DEBUG);

		
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=mise_a_jour.php?etape=4&version=$version&lang=$lang\">";
	}
	else
	{
		// on lance les etape (fonctions) séquentiellement :
		// avec un arret à la fin de chaque étape

		$sub_etape=( (isset($_GET['sub_etape'])) ? $_GET['sub_etape'] : ( (isset($_POST['sub_etape'])) ? $_POST['sub_etape'] : 0 ) ) ;

		if($sub_etape==0) { echo "<a href=\"$PHP_SELF?sub_etape=1&version=$version&lang=$lang\">start upgrade_from_v1.4.2</a><br>\n"; }
		if($sub_etape==1) { \install\Fonctions::e1_create_table_conges_appli( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=2&version=$version&lang=$lang\">sub_etape 1  OK</a><br>\n"; }
		if($sub_etape==2) { \install\Fonctions::e2_insert_into_conges_appli( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=3&version=$version&lang=$lang\">sub_etape 2  OK</a><br>\n"; }
		if($sub_etape==3) { \install\Fonctions::e3_delete_from_table_conges_config( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=4&version=$version&lang=$lang\">sub_etape 3  OK</a><br>\n"; }
		if($sub_etape==4) { \install\Fonctions::e4_alter_table_conges_users( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=5&version=$version&lang=$lang\">sub_etape 4  OK</a><br>\n"; }
		if($sub_etape==5) { \install\Fonctions::e5_insert_into_conges_config( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=6&version=$version&lang=$lang\">sub_etape 5  OK</a><br>\n"; }
		if($sub_etape==6) { \install\Fonctions::e6_alter_table_conges_solde_user( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=7&version=$version&lang=$lang\">sub_etape 6  OK</a><br>\n"; }
		if($sub_etape==7) { \install\Fonctions::e7_alter_tables_taille_login( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=8&version=$version&lang=$lang\">sub_etape 7  OK</a><br>\n"; }
		
		
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		if($sub_etape==8) { echo "<a href=\"mise_a_jour.php?etape=4&version=$version&lang=$lang\">upgrade_from_v1.4.2  OK</a><br>\n"; }
	}
