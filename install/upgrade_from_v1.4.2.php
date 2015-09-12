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
// SCRIPT DE MIGRATION DE LA VERSION 1.4.2 vers 1.5.0
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
		e1_create_table_conges_appli( $DEBUG);
		e2_insert_into_conges_appli( $DEBUG);
		e3_delete_from_table_conges_config( $DEBUG);
		e4_alter_table_conges_users( $DEBUG);
		e5_insert_into_conges_config( $DEBUG);
		e6_alter_table_conges_solde_user( $DEBUG);
		e7_alter_tables_taille_login( $DEBUG);

		
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=mise_a_jour.php?etape=4&version=$version&lang=$lang\">";
	}
	else
	{
		// on lance les etape (fonctions) séquentiellement :
		// avec un arret à la fin de chaque étape

		$sub_etape=( (isset($_GET['sub_etape'])) ? $_GET['sub_etape'] : ( (isset($_POST['sub_etape'])) ? $_POST['sub_etape'] : 0 ) ) ;

		if($sub_etape==0) { echo "<a href=\"$PHP_SELF?sub_etape=1&version=$version&lang=$lang\">start upgrade_from_v1.4.2</a><br>\n"; }
		if($sub_etape==1) { e1_create_table_conges_appli( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=2&version=$version&lang=$lang\">sub_etape 1  OK</a><br>\n"; }
		if($sub_etape==2) { e2_insert_into_conges_appli( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=3&version=$version&lang=$lang\">sub_etape 2  OK</a><br>\n"; }
		if($sub_etape==3) { e3_delete_from_table_conges_config( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=4&version=$version&lang=$lang\">sub_etape 3  OK</a><br>\n"; }
		if($sub_etape==4) { e4_alter_table_conges_users( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=5&version=$version&lang=$lang\">sub_etape 4  OK</a><br>\n"; }
		if($sub_etape==5) { e5_insert_into_conges_config( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=6&version=$version&lang=$lang\">sub_etape 5  OK</a><br>\n"; }
		if($sub_etape==6) { e6_alter_table_conges_solde_user( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=7&version=$version&lang=$lang\">sub_etape 6  OK</a><br>\n"; }
		if($sub_etape==7) { e7_alter_tables_taille_login( $DEBUG); echo "<a href=\"$PHP_SELF?sub_etape=8&version=$version&lang=$lang\">sub_etape 7  OK</a><br>\n"; }
		
		
		// on renvoit à la page mise_a_jour.php (là d'ou on vient)
		if($sub_etape==8) { echo "<a href=\"mise_a_jour.php?etape=4&version=$version&lang=$lang\">upgrade_from_v1.4.2  OK</a><br>\n"; }
	}


/********************************************************************************************************/
/********************************************************************************************************/
/***   FONCTIONS   ***/
/********************************************************************************************************/



/******************************************************************/
/***   ETAPE 1 : Creation de la table conges_appli              ***/
/******************************************************************/
function e1_create_table_conges_appli( $DEBUG=FALSE)
{

	$sql_create="CREATE TABLE IF NOT EXISTS `conges_appli` (
  					`appli_variable` varchar(100) binary NOT NULL default '',
  					`appli_valeur` varchar(200) binary NOT NULL default '',
  					PRIMARY KEY  (`appli_variable`)
					) DEFAULT CHARSET=latin1; ";
	$result_create = \includes\SQL::query($sql_create);

}


/*****************************************************************/
/***   ETAPE 2 : Ajout de paramètres dans  conges_appli       ***/
/*****************************************************************/
function e2_insert_into_conges_appli( $DEBUG=FALSE)
{

	$sql_insert_1="INSERT INTO `conges_appli` VALUES ('num_exercice', '1')";
	$result_insert_1 = \includes\SQL::query($sql_insert_1)  ;

	$sql_insert_2="INSERT INTO `conges_appli` VALUES ('date_limite_reliquats', '0')";
	$result_insert_2 = \includes\SQL::query($sql_insert_2)  ;

	$sql_insert_3="INSERT INTO `conges_appli` VALUES ('semaine_bgcolor', '#FFFFFF')";
	$result_insert_3 = \includes\SQL::query($sql_insert_3)  ;

	$sql_insert_4="INSERT INTO `conges_appli` VALUES ('week_end_bgcolor', '#BFBFBF')";
	$result_insert_4 = \includes\SQL::query($sql_insert_4)  ;

	$sql_insert_5="INSERT INTO `conges_appli` VALUES ('temps_partiel_bgcolor', '#FFFFC4')";
	$result_insert_5 = \includes\SQL::query($sql_insert_5)  ;

	$sql_insert_6="INSERT INTO `conges_appli` VALUES ('conges_bgcolor', '#DEDEDE')";
	$result_insert_6 = \includes\SQL::query($sql_insert_6)  ;

	$sql_insert_7="INSERT INTO `conges_appli` VALUES ('demande_conges_bgcolor', '#E7C4C4')";
	$result_insert_7 = \includes\SQL::query($sql_insert_7)  ;

	$sql_insert_8="INSERT INTO `conges_appli` VALUES ('absence_autre_bgcolor', '#D3FFB6')";
	$result_insert_8 = \includes\SQL::query($sql_insert_8)  ;

	$sql_insert_9="INSERT INTO `conges_appli` VALUES ('fermeture_bgcolor', '#7B9DE6')";
	$result_insert_9 = \includes\SQL::query($sql_insert_9)  ;

}


/**********************************************************************/
/***   ETAPE 3 : Suppression de paramètres dans conges_config       ***/
/**********************************************************************/
function e3_delete_from_table_conges_config( $DEBUG=FALSE)
{

	$sql_delete_1="DELETE FROM conges_config WHERE conf_type = 'hidden' ";
	$result_delete_1 = \includes\SQL::query($sql_delete_1)  ;

	$sql_delete_2="DELETE FROM conges_config WHERE conf_nom = 'rtt_comme_conges' ";
	$result_delete_2 = \includes\SQL::query($sql_delete_2)  ;
}


/******************************************************************/
/***   ETAPE 4 : Modif de la table conges_users   ***/
/******************************************************************/
function e4_alter_table_conges_users( $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$sql_alter_1=" ALTER TABLE `conges_users` ADD `u_num_exercice` INT(2) NOT NULL DEFAULT '0' ";
	$result_alter_1 = \includes\SQL::query($sql_alter_1)  ;

}

/*****************************************************************/
/***   ETAPE 5 : Ajout de paramètres dans  conges_config       ***/
/*****************************************************************/
function e5_insert_into_conges_config( $DEBUG=FALSE)
{

	$sql_insert_1="INSERT INTO `conges_config` VALUES ('autorise_reliquats_exercice', 'TRUE', '12_Fonctionnement de l\'Etablissement', 'boolean', 'config_comment_autorise_reliquats_exercice')";
	$result_insert_1 = \includes\SQL::query($sql_insert_1)  ;

	$sql_insert_2="INSERT INTO `conges_config` VALUES ('nb_maxi_jours_reliquats', '0', '12_Fonctionnement de l\'Etablissement', 'texte', 'config_comment_nb_maxi_jours_reliquats')";
	$result_insert_2 = \includes\SQL::query($sql_insert_2)  ;

	$sql_insert_3="INSERT INTO `conges_config` VALUES ('jour_mois_limite_reliquats', '0', '12_Fonctionnement de l\'Etablissement', 'texte', 'config_comment_jour_mois_limite_reliquats')";
	$result_insert_3 = \includes\SQL::query($sql_insert_3)  ;


}


/******************************************************************/
/***   ETAPE 6 : Modif de la table conges_solde_user   ***/
/******************************************************************/
function e6_alter_table_conges_solde_user( $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$sql_alter_1=" ALTER TABLE `conges_solde_user` ADD `su_reliquat` DECIMAL( 4, 2 ) NOT NULL DEFAULT '0' ";
	$result_alter_1 = \includes\SQL::query($sql_alter_1)  ;

}


/*********************************************************************/
/***   ETAPE 7 : Modif de la taille max du login dans les tables   ***/
/*********************************************************************/
function e7_alter_tables_taille_login( $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];

	$sql_alter_1=" ALTER TABLE `conges_artt` CHANGE `a_login` `a_login` VARBINARY( 99 ) NOT NULL ";
	$result_alter_1 = \includes\SQL::query($sql_alter_1)  ;

	$sql_alter_2=" ALTER TABLE `conges_echange_rtt` CHANGE `e_login` `e_login` VARBINARY( 99 ) NOT NULL ";
	$result_alter_2 = \includes\SQL::query($sql_alter_2)  ; 

	$sql_alter_3=" ALTER TABLE `conges_edition_papier` CHANGE `ep_login` `ep_login` VARBINARY( 99 ) NOT NULL ";
	$result_alter_3 = \includes\SQL::query($sql_alter_3)  ; 

	$sql_alter_4=" ALTER TABLE `conges_groupe_grd_resp` CHANGE `ggr_login` `ggr_login` VARBINARY( 99 ) NOT NULL ";
	$result_alter_4 = \includes\SQL::query($sql_alter_4)  ;  

	$sql_alter_5=" ALTER TABLE `conges_groupe_resp` CHANGE `gr_login` `gr_login` VARBINARY( 99 ) NOT NULL ";
	$result_alter_5 = \includes\SQL::query($sql_alter_5)  ;

	$sql_alter_6=" ALTER TABLE `conges_groupe_users` CHANGE `gu_login` `gu_login` VARBINARY( 99 ) NOT NULL ";
	$result_alter_6 = \includes\SQL::query($sql_alter_6)  ;

	$sql_alter_7=" ALTER TABLE `conges_logs` CHANGE `log_user_login_par` `log_user_login_par` VARBINARY( 99 ) NOT NULL , CHANGE `log_user_login_pour` `log_user_login_pour` VARBINARY( 99 ) NOT NULL ";
	$result_alter_7 = \includes\SQL::query($sql_alter_7)  ; 
 
	$sql_alter_8=" ALTER TABLE `conges_periode` CHANGE `p_login` `p_login` VARBINARY( 99 ) NOT NULL ";
	$result_alter_8 = \includes\SQL::query($sql_alter_8)  ;  

	$sql_alter_9=" ALTER TABLE `conges_solde_user` CHANGE `su_login` `su_login` VARBINARY( 99 ) NOT NULL ";
	$result_alter_9 = \includes\SQL::query($sql_alter_9)  ;
 
	$sql_alter_10=" ALTER TABLE `conges_users` CHANGE `u_login` `u_login` VARBINARY( 99 ) NOT NULL , CHANGE `u_resp_login` `u_resp_login` VARBINARY( 99 ) NULL DEFAULT NULL ";
	$result_alter_10 = \includes\SQL::query($sql_alter_10)  ;
	 
}




