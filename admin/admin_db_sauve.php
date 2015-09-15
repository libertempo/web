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

include_once CONFIG_PATH .'config_ldap.php';
include_once ROOT_PATH .'fonctions_conges.php' ;
include_once INCLUDE_PATH .'fonction.php';
include_once INCLUDE_PATH .'session.php';

$DEBUG=FALSE ;
//$DEBUG=TRUE ;

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin", $DEBUG);


	/*** initialisation des variables ***/
	/*************************************/
	// recup des parametres reçus :
	// SERVER
	$PHP_SELF=$_SERVER['PHP_SELF'];
	// GET / POST
	$choix_action    = getpost_variable('choix_action');
	$type_sauvegarde = getpost_variable('type_sauvegarde');
	$commit          = getpost_variable('commit');

	$fichier_restaure_name="";
	$fichier_restaure_tmpname="";
	$fichier_restaure_size=0;
	$fichier_restaure_error=4;
	if(isset($_FILES['fichier_restaure']))
	{
		$fichier_restaure_name=$_FILES['fichier_restaure']['name'];
		$fichier_restaure_size=$_FILES['fichier_restaure']['size'];
		$fichier_restaure_tmpname=$_FILES['fichier_restaure']['tmp_name'];
		$fichier_restaure_error=$_FILES['fichier_restaure']['error'];
	}
	/*************************************/
	if( $DEBUG ) {	echo "_FILES = <br>\n"; print_r($_FILES); echo "<br>\n"; }


	if($choix_action=="")
		choix_save_restore($DEBUG);
	elseif($choix_action=="sauvegarde")
	{
		if( (!isset($type_sauvegarde)) || ($type_sauvegarde=="") )
			choix_sauvegarde($DEBUG);
		else
		{
			if( (!isset($commit)) || ($commit=="") )
				sauve($type_sauvegarde);
			else
				commit_sauvegarde($type_sauvegarde, $DEBUG);
		}
	}
	elseif($choix_action=="restaure")
	{
		if( (!isset($fichier_restaure_name)) || ($fichier_restaure_name=="")||(!isset($fichier_restaure_tmpname)) || ($fichier_restaure_tmpname=="") )
			choix_restaure($DEBUG);
		else
			restaure($fichier_restaure_name, $fichier_restaure_tmpname, $fichier_restaure_size, $fichier_restaure_error, $DEBUG);
	}
	else
		/* APPEL D'UNE AUTRE PAGE immediat */
		echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=admin_index.php?session=$session&onglet=admin-users\">";




/**********  FONCTIONS  ****************************************/

// CHOIX
function choix_save_restore($DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	header_popup();	

	echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr>\n";
	echo "<th colspan=\"2\">". _('admin_sauve_db_choisissez') ." :</th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td><input type=\"radio\" name=\"choix_action\" value=\"sauvegarde\" checked></td>\n";
	echo "<td><b> ". _('admin_sauve_db_sauve') ."</b></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td><input type=\"radio\" name=\"choix_action\" value=\"restaure\" /></td>\n";
	echo "<td><b> ". _('admin_sauve_db_restaure') ."</b></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	&nbsp;\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"submit\" value=\"". _('form_submit') ."\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";
	
	bottom();

}


// SAUVEGARDE
function choix_sauvegarde($DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	
	header_popup();	
	
	echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr>\n";
	echo "<th colspan=\"2\">". _('admin_sauve_db_options') ."</th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td><input type=\"radio\" name=\"type_sauvegarde\" value=\"all\" checked></td>\n";
	echo "	<td>". _('admin_sauve_db_complete') ."</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td><input type=\"radio\" name=\"type_sauvegarde\" value=\"data\"></td>\n";
	echo "	<td>". _('admin_sauve_db_data_only') ."</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	&nbsp;\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td colspan=\"2\" align=\"center\">\n";
	echo "		<input type=\"hidden\" name=\"choix_action\" value=\"sauvegarde\">\n";
	echo "		<input type=\"submit\" value=\"". _('admin_sauve_db_do_sauve') ."\">\n";
	echo "	</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";

	bottom();

}

function sauve($type_sauvegarde, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	redirect(ROOT_PATH .'admin/admin_db_sauve.php?session='.$session.'&choix_action=sauvegarde&type_sauvegarde='.$type_sauvegarde.'&commit=ok', false);
	
	header_popup();	
	
	echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";

	echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr>\n";
	echo "<th colspan=\"2\">". _('admin_sauve_db_save_ok') ." ...</th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td colspan=\"2\" align=\"center\">\n";
	echo "	<input type=\"button\" value=\"". _('form_close_window') ."\" onClick=\"javascript:window.close();\">\n";
	echo "</td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</form>\n";

	bottom();
}

function commit_sauvegarde($type_sauvegarde, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	
	header("Pragma: no-cache");
	header("Content-Type: text/x-delimtext; name=\"php_conges_".$type_sauvegarde.".sql\"");
	header("Content-disposition: attachment; filename=php_conges_".$type_sauvegarde.".sql");

	//
	// Build the sql script file...
	//
	$maintenant=date("d-m-Y H:i:s");
	echo "#\n";
	echo "# PHP_CONGES\n";
	echo "#\n# DATE : $maintenant\n";
	echo "#\n";

	//recup de la liste des tables
	$sql1="SHOW TABLES";
	$ReqLog = \includes\SQL::query($sql1) ;
	while ($resultat = $ReqLog->fetch_array())
	{
		$table=$resultat[0] ;

		echo "#\n#\n# TABLE: $table \n#\n";
		if(($type_sauvegarde=="all") || ($type_sauvegarde=="structure") )
		{
			echo "# Struture : \n#\n";
			echo get_table_structure($table);
		}
		if(($type_sauvegarde=="all") || ($type_sauvegarde=="data") )
		{
			echo "# Data : \n#\n";
			echo get_table_data($table);
		}
	}


}


// RESTAURATION
function choix_restaure($DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	header_popup();	
	
	echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";
	echo "<form enctype=\"multipart/form-data\" action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
	echo "<table>\n";
	echo "<tr>\n";
	echo "<th>". _('admin_sauve_db_restaure') ."<br>". _('admin_sauve_db_file_to_restore') ." :</th>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\"> <input type=\"file\" name=\"fichier_restaure\"> </td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\">&nbsp;</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\"> <font color=\"red\">". _('admin_sauve_db_warning') ." !</font> </td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td align=\"center\">&nbsp;</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "	<td align=\"center\">\n";
	echo "		<input type=\"hidden\" name=\"choix_action\" value=\"restaure\">\n";
	echo "		<input type=\"submit\" value=\"". _('admin_sauve_db_do_restaure') ."\">\n";
	echo "	</td>\n";
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


function restaure($fichier_restaure_name, $fichier_restaure_tmpname, $fichier_restaure_size, $fichier_restaure_error, $DEBUG=FALSE)
{
	$PHP_SELF=$_SERVER['PHP_SELF'];
	$session=session_id();

	
	header_popup();	
	
	echo "<h1>". _('admin_sauve_db_titre') ."</h1>\n";

	if( ($fichier_restaure_error!=0)||($fichier_restaure_size==0) ) // s'il y a eu une erreur dans le telechargement OU taille==0
	//(cf code erreur dans fichier features.file-upload.errors.html de la doc php)
	{
		//message d'erreur et renvoit sur la page précédente (choix fichier)

		echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
		echo "<table>\n";
		echo "<tr>\n";
		echo "<th> ". _('admin_sauve_db_bad_file') ." : <br>$fichier_restaure_name</th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td align=\"center\">\n";
		echo "	<input type=\"hidden\" name=\"choix_action\" value=\"restaure\">\n";
		echo "	<input type=\"submit\" value=\"". _('form_redo') ."\">\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td align=\"center\">\n";
		echo "	<input type=\"button\" value=\"". _('form_cancel') ."\" onClick=\"javascript:window.close();\">\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n";

	}
	else
	{

		$result = execute_sql_file($fichier_restaure_tmpname, $DEBUG);

		echo "<form action=\"\" method=\"POST\">\n";
		echo "<table>\n";
		echo "<tr>\n";
		echo "<th>". _('admin_sauve_db_restaure_ok') ." !</th>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td align=\"center\">&nbsp;</td>\n";
		echo "</tr>\n";
		echo "<tr>\n";
		echo "<td align=\"center\">\n";
		echo "	<input type=\"button\" value=\"". _('form_close_window') ."\" onClick=\"javascript:window.close();\">\n";
		echo "</td>\n";
		echo "</tr>\n";
		echo "</table>\n";
		echo "</form>\n";

	}

	bottom();


}




// recup de la structure d'une table sous forme de CREATE ...
function get_table_structure($table, $DEBUG=FALSE)
{


	$chaine_drop="DROP TABLE IF EXISTS  `$table` ;\n";
	$chaine_create = "CREATE TABLE `$table` ( ";

	// description des champs :
	$sql_champs='SHOW FIELDS FROM '. \includes\SQL::quote($table);
	$ReqLog_champs = \includes\SQL::query($sql_champs) ;
	$count_champs=$ReqLog_champs->num_rows;
	$i=0;
	while ($resultat_champs = $ReqLog_champs->fetch_array())
	{
		$sql_field=$resultat_champs['Field'];
		$sql_type=$resultat_champs['Type'];
		$sql_null=$resultat_champs['Null'];
		$sql_key=$resultat_champs['Key'];
		$sql_default=$resultat_champs['Default'];
		$sql_extra=$resultat_champs['Extra'];

		$chaine_create=$chaine_create." `$sql_field` $sql_type ";
		if($sql_null != "YES")
			$chaine_create=$chaine_create." NOT NULL ";
		if(!empty($sql_default))
		{
			if($sql_default=="CURRENT_TIMESTAMP")
				$chaine_create=$chaine_create." default $sql_default ";		// pas de quotes !
			else
				$chaine_create=$chaine_create." default '$sql_default' ";
		}
		if(!empty($sql_extra))
			$chaine_create=$chaine_create." $sql_extra ";
		if($i<$count_champs-1)
			$chaine_create=$chaine_create.",";
		$i++;
	}

	// description des index :
	$sql_index = 'SHOW KEYS FROM '. \includes\SQL::quote($table).'';
	$ReqLog_index = \includes\SQL::query($sql_index) ;
	$count_index=$ReqLog_index->num_rows;
	$i=0;

	// il faut faire une liste pour prendre les PRIMARY, le nom de la colonne et
	// genérer un PRIMARY KEY ('key1'), PRIMARY KEY ('key2', ...)
	// puis on regarde ceux qui ne sont pas PRIMARY et on regarde s'ils sont UNIQUE ou pas et
	// on génére une liste= UNIQUE 'key1' ('key1') , 'key2' ('key2') , ....
	// ou une liste= KEY key1' ('key1') , 'key2' ('key2') , ....
	$list_primary="";
	$list_unique="";
	$list_key="";
	while ($resultat_index = $ReqLog_index->fetch_array())
	{
		$sql_key_name=$resultat_index['Key_name'];
		$sql_column_name=$resultat_index['Column_name'];
		$sql_non_unique=$resultat_index['Non_unique'];

		if($sql_key_name=="PRIMARY")
		{
			if($list_primary=="")
				$list_primary=" PRIMARY KEY (`$sql_column_name` ";
			else
				$list_primary=$list_primary.", `$sql_column_name` ";
		}
		elseif($sql_non_unique== 0)
		{
			if($list_unique=="")
				$list_unique=" UNIQUE  `$sql_column_name` (`$sql_key_name`) ";
			else
				$list_unique=$list_unique.", `$sql_column_name` (`$sql_key_name`) ";
		}
		else
		{
			if($list_key=="")
				$list_key=" KEY  `$sql_column_name` (`$sql_key_name`) ";
			else
				$list_key=$list_key.", KEY `$sql_column_name` (`$sql_key_name`) ";
		}
	}

	if($list_primary!="")
		$list_primary=$list_primary." ) ";

	if($list_primary!="")
		$chaine_create=$chaine_create.",    ".$list_primary;
	if($list_unique!="")
		$chaine_create=$chaine_create.",    ".$list_unique;
	if($list_key!="")
		$chaine_create=$chaine_create.",    ".$list_key;

	$chaine_create=$chaine_create." ) DEFAULT CHARSET=latin1;\n#\n";

	return($chaine_drop.$chaine_create);

}


// recup des data d'une table sous forme de INSERT ...
function get_table_data($table,  $DEBUG=FALSE)
{

	$chaine_data="";

	// suppression des donnéées de la table :
	$chaine_delete='DELETE FROM `'. \includes\SQL::quote($table).'` ;'."\n";
	$chaine_data=$chaine_data.$chaine_delete ;

	// recup des donnéées de la table :
	$sql_data='SELECT * FROM '. \includes\SQL::quote($table);
	$ReqLog_data = \includes\SQL::query($sql_data);

	while ($resultat_data = $ReqLog_data->fetch_array())
	{
		$count_fields=count($resultat_data)/2;   // on divise par 2 car c'est un tableau indexé (donc compte key+valeur)
		$chaine_insert = "INSERT INTO `$table` VALUES ( ";
		for($i=0; $i<$count_fields; $i++)
		{
			if(isset($resultat_data[$i]))
				$chaine_insert = $chaine_insert."'".addslashes($resultat_data[$i])."'";
			else
				$chaine_insert = $chaine_insert."NULL";

			if($i!=$count_fields-1)
				$chaine_insert = $chaine_insert.", ";
		}
		$chaine_insert = $chaine_insert." );\n";

		$chaine_data=$chaine_data.$chaine_insert;
	}

	return $chaine_data;
}

