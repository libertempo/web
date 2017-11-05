<?php

define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';
include_once INCLUDE_PATH . 'fonction.php';

if (empty(session_id())) {
	redirect(ROOT_PATH . 'index.php?return_url=config/index.php');
}


include_once ROOT_PATH .'fonctions_conges.php' ;

$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
include_once INCLUDE_PATH .'session.php';

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

// verif des droits du user à afficher la page
verif_droits_user("is_admin");

$_SESSION['from_config']=TRUE;  // initialise ce flag pour changer le bouton de retour des popup

	$onglet = htmlentities(getpost_variable('onglet'), ENT_QUOTES | ENT_HTML401);

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
			<a href="'.$PHP_SELF.'?onglet='.$key.'">'. $title .'</a>
		</div>';
	}
	echo '</div>';

	echo '<div class="'.$onglet.' wrapper">';

		echo '<a href="' . ROOT_PATH . "admin/admin_index.php\" class=\"admin-back\"><i class=\"fa fa-arrow-circle-o-left\"></i>". _('form_retour')."</a>\n";

		if($onglet == 'general') {
			include_once ROOT_PATH . 'config/configure.php';
		}
		else {
			include_once ROOT_PATH . 'config/config_'.$onglet.'.php';
		}

	echo '</div>';
