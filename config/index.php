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
include INCLUDE_PATH . 'fonction.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$session =(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

if (empty($session)) {
	redirect(ROOT_PATH . 'index.php?return_url=config/index.php');
}


include ROOT_PATH .'fonctions_conges.php' ;

$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
include INCLUDE_PATH .'session.php';

//include'fonctions_install.php' ;
	
$PHP_SELF=$_SERVER['PHP_SELF'];

$session=session_id();

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin");

$_SESSION['from_config']=TRUE;  // initialise ce flag pour changer le bouton de retour des popup
// propose_config();

	$onglet = getpost_variable('onglet');
	if(!$onglet)
		$onglet = 'general';
	
	/*********************************/
	/*   COMPOSITION DES ONGLETS...  */
	/*********************************/

	$onglets = array();
	
	$onglets['general'] = _('install_config_appli');

	$onglets['type_absence'] = _('install_config_types_abs');
		
	$onglets['mail'] = _('install_config_mail');
	
	$onglets['logs'] = _('config_logs');
	
	/*********************************/
	/*   COMPOSITION DU HEADER...    */
	/*********************************/
	
	$add_css = '<style>#onglet_menu .onglet{ width: '. (str_replace(',', '.', 100 / count($onglets) )).'% ;}</style>';
	header_menu('config','',$add_css);
	
	
	/*********************************/
	/*   AFFICHAGE DES ONGLETS...  */
	/*********************************/
	
	echo '<div id="onglet_menu">';
	foreach($onglets as $key => $title) {
		echo '<div class="onglet '.($onglet == $key ? ' active': '').'" >
			<a href="'.$PHP_SELF.'?session='.$session.'&onglet='.$key.'">'. $title .'</a>
		</div>';
	}
	echo '</div>';

	echo '<div class="'.$onglet.' wrapper">';

		echo '<a href="' . ROOT_PATH . "admin/admin_index.php?session=$session\" class=\"admin-back\"><i class=\"fa fa-arrow-circle-o-left\"></i>Retour mode admin</a>\n";

		if($onglet == 'general') {
			include ROOT_PATH . 'config/configure.php';
		}
		else {
			include ROOT_PATH . 'config/config_'.$onglet.'.php';
		}
		
	echo '</div>';
	


// /*****************************************************************************/
// /*   FONCTIONS   */


// function propose_config()
// {
// 	$session=session_id();
	
// 	header_menu('CONGES : Configuration', $_SESSION['config']['titre_admin_index']);

// 	// affichage du titre
// 	echo "<center>\n";
// 	echo "<br><H1><img src=\"". TEMPLATE_PATH . "img/tux_config_32x32.png\" width=\"32\" height=\"32\" border=\"0\" title=\"". _('install_install_phpconges') ."\" alt=\"". _('install_install_phpconges') ."\"> ". _('install_index_titre') ."</H1>\n";
// 	echo "<br><br>\n";
	
// 		echo "<h2>". _('install_configuration') ." :</h2>\n";
// 		echo "<h3>\n";
// 		echo "<table border=\"0\">\n";
// 		echo "<tr><td>-> <a href=\"configure.php?session=$session\">". _('install_config_appli') ."</a></td></tr>\n";
// 		echo "<tr><td>-> <a href=\"config_type_absence.php?session=$session\">". _('install_config_types_abs') ."</a></td></tr>\n";
// 		echo "<tr><td>-> <a href=\"config_mail.php?session=$session\">". _('install_config_mail') ."</a></td></tr>\n";
// 		echo "<tr><td>-> <a href=\"javascript:void(0);\" onClick=\"javascript:OpenPopUp('test_mail.php?session=$session','testmail',800,350);\">". _('install_test_mail') ."</a></td></tr>\n";
// 		echo "<tr><td>-> <a href=\"config_logs.php?session=$session\">". _('config_logs') ."</a></td></tr>\n";
// 		echo "<tr><td>&nbsp;</td></tr>\n";
// 		echo "<tr><td>-> <a href=\"../\">". _('install_acceder_appli') ."</a></td></tr>\n";
// 		echo "</table>\n";
// 		echo "</h3><br><br>\n";
		

// 		// echo '<a href="'. ROOT_PATH .'deconnexion.php?session='.$session.'" target="_top">' .
// 		// 		'<img src="'. TEMPLATE_PATH . 'img/exit.png" width="22" height="22" border="0" title="'. _('button_deconnect') .'" alt="'. _('button_deconnect') .'">' .
// 		// 		 _('button_deconnect') .'</a>';


// 	bottom();
// }

