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

include_once ROOT_PATH .'fonctions_conges.php' ;
include_once INCLUDE_PATH .'fonction.php';

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
		
	\install\Fonctions::lance_install($lang, $DEBUG); 
	
	bottom();

