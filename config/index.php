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
include_once INCLUDE_PATH . 'fonction.php';

$session =(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

if (empty($session)) {
	redirect(ROOT_PATH . 'index.php?return_url=config/index.php');
}


include_once ROOT_PATH .'fonctions_conges.php' ;

$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
include_once INCLUDE_PATH .'session.php';

$PHP_SELF=$_SERVER['PHP_SELF'];

$session=session_id();

// verif des droits du user à afficher la page
verif_droits_user($session, "is_admin");

$_SESSION['from_config']=TRUE;  // initialise ce flag pour changer le bouton de retour des popup

	$onglet = getpost_variable('onglet');
	
	if(!$onglet && $_SESSION['userlogin']=="admin")
	{
		$onglet = 'general';
	} elseif (!$onglet && $_SESSION['userlogin']!="admin") {

		if($_SESSION['config']['affiche_bouton_config_pour_admin'])
			$onglet = 'general';
		elseif($_SESSION['config']['affiche_bouton_config_absence_pour_admin'])
			$onglet = 'type_absence';
		elseif($_SESSION['config']['affiche_bouton_config_mail_pour_admin'])
			$onglet = 'config_mail';
		
	} 
	
	/*********************************/
	/*   COMPOSITION DES ONGLETS...  */
	/*********************************/

	$onglets = array();

	if($_SESSION['config']['affiche_bouton_config_pour_admin'] || $_SESSION['userlogin']=="admin")
		$onglets['general'] = _('install_config_appli');

	if($_SESSION['config']['affiche_bouton_config_absence_pour_admin'] || $_SESSION['userlogin']=="admin")
		$onglets['type_absence'] = _('install_config_types_abs');

	if($_SESSION['config']['affiche_bouton_config_mail_pour_admin'] || $_SESSION['userlogin']=="admin")
		$onglets['mail'] = _('install_config_mail');
	
	$onglets['logs'] = _('config_logs');
	
	/*********************************/
	/*   COMPOSITION DU HEADER...    */
	/*********************************/
	
	$add_css = '<style>#onglet_menu .onglet{ width: '. (str_replace(',', '.', 100 / count($onglets) )).'% ;}</style>';
	header_menu('', 'Libertempo : '._('admin_button_config_1'),$add_css);
	
	
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

		echo '<a href="' . ROOT_PATH . "admin/admin_index.php?session=$session\" class=\"admin-back\"><i class=\"fa fa-arrow-circle-o-left\"></i>". _('form_retour')."</a>\n";

		if($onglet == 'general') {
			include_once ROOT_PATH . 'config/configure.php';
		}
		else {
			include_once ROOT_PATH . 'config/config_'.$onglet.'.php';
		}
		
	echo '</div>';

