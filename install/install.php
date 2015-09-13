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

include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';
include'fonctions_install.php' ;
	
$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=FALSE;
//$DEBUG=TRUE;

//recup de la langue
$lang=(isset($_GET['lang']) ? $_GET['lang'] : ((isset($_POST['lang'])) ? $_POST['lang'] : "") ) ;

if( $DEBUG ) { echo "SESSION = <br>\n"; print_r($_SESSION); echo "<br><br>\n"; }

	
	header_popup('PHP_CONGES : Installation');
	
	// affichage du titre
	echo "<center>\n";
	echo "<br><H1><img src=\"". TEMPLATE_PATH ."img/tux_config_32x32.png\" width=\"32\" height=\"32\" border=\"0\" title=\"". _('install_install_phpconges') ."\" alt=\"". _('install_install_phpconges') ."\"> ". _('install_install_titre') ."</H1>\n";
	echo "<br><br>\n";
		
	lance_install($lang, $DEBUG); 
	
	bottom();


/*****************************************************************************/
/*   FONCTIONS   */

// install la nouvelle version dans une database vide ... et config
function lance_install($lang, $DEBUG=FALSE)
{

	$PHP_SELF=$_SERVER['PHP_SELF'];
	
	include CONFIG_PATH .'dbconnect.php' ;
	include ROOT_PATH .'version.php' ;
	
	//verif si create / alter table possible !!!
	if( !test_create_table( $DEBUG) )
	{
		echo "<font color=\"red\"><b>CREATE TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
		echo "<br>". _('install_puis') ." ...<br>\n";
		echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
		echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
		echo "</form>\n";
	}
	elseif( !test_drop_table( $DEBUG) )
	{
		echo "<font color=\"red\"><b>DROP TABLE</b> ". _('install_impossible_sur_db') ." <b>$mysql_database</b> (". _('install_verif_droits_mysql') ." <b>$mysql_user</b>)...</font><br> \n";
		echo "<br>". _('install_puis') ." ...<br>\n";
		echo "<form action=\"$PHP_SELF\" method=\"POST\">\n";
		echo "<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
		echo "</form>\n";
	}
	else
	{
		//on execute le script [nouvelle vesion].sql qui crée et initialise les tables 
		$file_sql="sql/php_conges_v$config_php_conges_version.sql";
		if(file_exists($file_sql))
			$result = execute_sql_file($file_sql,  $DEBUG);
		
		
		/*************************************/
		// FIN : mise à jour de la "installed_version" et de la langue dans la table conges_config
		$sql_update_version="UPDATE conges_config SET conf_valeur = '$config_php_conges_version' WHERE conf_nom='installed_version' ";
		$result_update_version = SQL::query($sql_update_version) ;

		$sql_update_lang="UPDATE conges_config SET conf_valeur = '$lang' WHERE conf_nom='lang' ";
		$result_update_lang = SQL::query($sql_update_lang) ;
		
		$tab_url=explode("/", $_SERVER['PHP_SELF']);

		array_pop($tab_url);
		array_pop($tab_url);
		
		$url_accueil= implode("/", $tab_url) ;  // on prend l'url complet sans le /install/install.php à la fin
		
		$sql_update_lang="UPDATE conges_config SET conf_valeur = '$url_accueil' WHERE conf_nom='URL_ACCUEIL_CONGES' ";
		$result_update_lang = SQL::query($sql_update_lang) ;
		
		
		$comment_log = "Install de php_conges (version = $config_php_conges_version) ";
		log_action(0, "", "", $comment_log,  $DEBUG);

		/*************************************/
		// on propose la page de config ....
		echo "<br><br><h2>". _('install_ok') ." !</h2><br>\n";
		
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"2; URL=../config/\">";
	}
}

