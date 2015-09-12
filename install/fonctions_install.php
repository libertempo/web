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

// teste le fichier config.php 
//renvoit TRUE si ok, et FALSE sinon
function test_config_file() {
	return is_readable( CONFIG_PATH .'config.php' );
}


// teste le fichier dbconnect.php 
//renvoit TRUE si ok, et FALSE sinon
function test_dbconnect_file() {
	return is_readable( CONFIG_PATH .'dbconnect.php' ) ;
}


// teste l'ancien fichier de conf config_old.php // mis par le user pour upgrade v1.0 to v1.1
//renvoit TRUE si ok, et FALSE sinon
function test_old_config_file() {
	return is_readable('config_old.php');
}


// teste l'existance et la conexion à la database
//renvoit TRUE si ok, et FALSE sinon
function test_database() {
	try {
		\includes\SQL::singleton();
	}
	catch (Exception $e){
		return false;
	}
	return \includes\SQL::getVar('connect_errno') == 0 ;
}



// renvoit le num de la version installée ou 0 s'il est inaccessible (non renseigné ou table non présente) 
function get_installed_version() {
	try {
		$reglog = \includes\SQL::query('show tables like \'conges_config\';');
		if( $reglog->num_rows == 0)
			return 0;
		$sql="SELECT conf_valeur FROM conges_config WHERE conf_nom='installed_version' ";
		if($reglog = \includes\SQL::query($sql))
			if($result=$reglog->fetch_array())
				return $result['conf_valeur'];
	}
	catch(Exception $e) {
		return 0;
	}
	return 0;
	
}



// teste la creation de table (verif si le user a les droits suffisants ou pas)
// renvoit TRUE ou FALSE
function test_create_table() {
	/*********************************************/
	// creation de la table `conges_test`
	$sql_create="CREATE TABLE IF NOT EXISTS `conges_test` (
				`test1` varchar(100) BINARY NOT NULL default '',
				`test2` varchar(100) BINARY NOT NULL default '',
 				 PRIMARY KEY  (`test1`)
				) ;";
	return \includes\SQL::query($sql_create);
}


// teste "alter table" (verif si le user a les droits suffisants ou pas)
// renvoit TRUE ou FALSE
function test_alter_table() {
	/*********************************************/
	// alter de la table `conges_test`
	$sql_alter="ALTER TABLE `conges_test` CHANGE `test2` `test2` varchar(150) ;" ;
	return \includes\SQL::query($sql_alter) ;
}


// teste la suppression de table (verif si le user a les droits suffisants ou pas)
// renvoit TRUE ou FALSE
function test_drop_table() {
	/*********************************************/
	// suppression de la table `conges_test`
	$sql_drop="DROP TABLE `conges_test` ;" ;
	return \includes\SQL::query($sql_drop);
}

function write_db_config($server,$user,$passwd,$db){
	if (is_writable( CONFIG_PATH .'dbconnect_new.php' ))
	{
		$newdbconnect = file_get_contents(CONFIG_PATH .'dbconnect_new.php');
		$newdbconnect .= "\n".'$mysql_serveur="'.$server.'" ;'."\n".'$mysql_user="'.$user.'" ;'."\n".'$mysql_pass="'.$passwd.'" ;'."\n".'$mysql_database="'.$db."\" ;\n";
		file_put_contents(CONFIG_PATH .'dbconnect.php', $newdbconnect);
		return true;
	}
	else
	{
		return false;
	}

}

