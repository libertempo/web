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
require ROOT_PATH . 'define.php';

//include ROOT_PATH .'fonctions_conges.php' ;
session_start();
$_SESSION['lang'] = 'fr_FR';

include INCLUDE_PATH .'fonction.php';

include ROOT_PATH .'fonctions_conges.php' ;

$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=FALSE;
//$DEBUG=TRUE;

$session=session_id();

// verif des droits du user à afficher la page
//verif_droits_user($session, "is_admin", $DEBUG);

//recup de la langue
$lang=(isset($_GET['lang']) ? $_GET['lang'] : ((isset($_POST['lang'])) ? $_POST['lang'] : "") ) ;

//recup de la config db
$dbserver=(isset($_GET['dbserver']) ? $_GET['dbserver'] : ((isset($_POST['dbserver'])) ? $_POST['dbserver'] : "") ) ;
$dbuser=(isset($_GET['dbuser']) ? $_GET['dbuser'] : ((isset($_POST['dbuser'])) ? $_POST['dbuser'] : "") ) ;
$dbpasswd=(isset($_GET['dbpasswd']) ? $_GET['dbpasswd'] : ((isset($_POST['dbpasswd'])) ? $_POST['dbpasswd'] : "") ) ;
$dbdb=(isset($_GET['dbdb']) ? $_GET['dbdb'] : ((isset($_POST['dbdb'])) ? $_POST['dbdb'] : "") ) ;

	if($lang=="")
	{
		header_popup();
		echo "<br><br>\n";
		echo "Choisissez votre langue :<br> \n";
		echo "Choose your language :<br>\n";
			echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
			// affichage de la liste des langues supportées ...
			// on lit le contenu du répertoire lang et on parse les nom de ficher (ex lang_fr_francais.php)
			affiche_select_from_lang_directory("", "");

			echo "<br>\n";
			echo "<input type=\"submit\" value=\"OK\">\n";
			echo "</form>\n";
		bottom();
	}
	elseif(\install\Fonctions::test_dbconnect_file($DEBUG)!=TRUE)
	{
		$_SESSION['langue']=$lang;      // sert ensuite pour mettre la langue dans la table config
//		$tab_lang_file = glob("lang/lang_".$lang.'_*.php');
//		include$tab_lang_file[0] ;
//		include$lang_file ;

		header_popup();
		echo "<center>\n";
		echo "<br><br>\n";
		if($dbserver=="" || $dbuser=="" || $dbpasswd=="")
		{
			echo  _('db_configuration');
			echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
			echo "Server :";
			echo '<INPUT type="text" value="localhost" name="dbserver"><br>';
			echo "Database name :";
			echo '<INPUT type="text" value="db_conges" name="dbdb"><br>';
			echo "\n User : ";
			echo '<INPUT type="text" value="conges" name="dbuser"><br>';
			echo "\n Password : ";
			echo '<INPUT type="password" name="dbpasswd"><br>';
			echo "<INPUT type=\"hidden\" value=\"".$lang."\" name=\"lang\"><br>";
			echo "<br>\n";
			echo "<input type=\"submit\" value=\"OK\">\n";
			echo "</form>\n";
			
		}
		else
		{
			$is_dbconf_ok= \install\Fonctions::write_db_config($dbserver,$dbuser,$dbpasswd,$dbdb);
			if($is_dbconf_ok!=true)
			{
				echo "le dossier ".CONFIG_PATH." n'est pas accessible en écriture";
			}
			else
			{
				echo _('db_configuration_ok');
				echo "<br><a href=\"$PHP_SELF?session=$session&lang=$lang\"> continuez....</a><br>\n";
			}


		}
		bottom();
	}
	else
	{
		include CONFIG_PATH .'dbconnect.php';
		include ROOT_PATH .'version.php';

		if(!\install\Fonctions::test_database())
		{
			header_popup();
			echo "<center>\n";
			echo "<br><br>\n";
			echo "<b>". _('install_db_inaccessible') ." ... <br><br>\n";
			echo  _('install_verifiez_param_file');
			echo "(". _('install_verifiez_priv_mysql') .")<br><br>\n";

			echo "<center>\n";
			echo "<br><br>\n";
			if($dbserver=="" || $dbuser=="" || $dbpasswd=="")
			{
				echo  _('db_configuration');
				echo "<form action=\"$PHP_SELF?session=$session\" method=\"POST\">\n";
				echo "Server :";
				echo '<INPUT type="text" value="localhost" name="dbserver"><br>';
				echo "Database name :";
				echo '<INPUT type="text" value="db_conges" name="dbdb"><br>';
				echo "\n User : ";
				echo '<INPUT type="text" value="conges" name="dbuser"><br>';
				echo "\n Password : ";
				echo '<INPUT type="password" name="dbpasswd"><br>';
				echo "<INPUT type=\"hidden\" value=\"".$lang."\" name=\"lang\"><br>";
				echo "<br>\n";
				echo "<input type=\"submit\" value=\"OK\">\n";
				echo "</form>\n";
			
		}
		else
		{
			$is_dbconf_ok=write_db_config($dbserver,$dbuser,$dbpasswd,$dbdb);
			if($is_dbconf_ok!=true)
			{
				echo "le dossier ".CONFIG_PATH." n'est pas accessible en écriture";
			}
			else
			{
				echo _('db_configuration_ok');
				echo "<br><a href=\"$PHP_SELF?session=$session&lang=$lang\"> continuez....</a><br>\n";
			}


		}

			bottom();
		}
		else
		{
			$installed_version = \install\Fonctions::get_installed_version( $DEBUG);

			if($installed_version==0)   // num de version inconnu
			{
				\install\Fonctions::install($lang,  $DEBUG);
			}
			else
			{
				// on compare la version déclarée dans la database avec la version déclarée dans le fichier de config
				if($installed_version != $config_php_conges_version)
				{
					// on attaque une mise a jour à partir de la version installée
					echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=mise_a_jour.php?version=$installed_version&lang=$lang\">";
				}
				else
				{
					// pas de mise a jour a faire : on propose les pages de config
					echo "<META HTTP-EQUIV=REFRESH CONTENT=\"0; URL=../config/\">";
				}
			}

			
		}
	}
