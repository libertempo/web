<?php
defined('ROOT_PATH') or define('ROOT_PATH', '../');
defined('INCLUDE_PATH') or define('INCLUDE_PATH',     ROOT_PATH . 'includes/');

require_once INCLUDE_PATH . 'define.php';

if (empty(session_id())) {
	redirect(ROOT_PATH . 'authentification?return_url=config/general');
}


$config = new \App\Libraries\Configuration(\includes\SQL::singleton());

$_SESSION['config']=init_config_tab();      // on initialise le tableau des variables de config
include_once INCLUDE_PATH .'session.php';

$PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);

// verif des droits du user à afficher la page
verif_droits_user("is_admin");

$_SESSION['from_config']=true;  // initialise ce flag pour changer le bouton de retour des popup

	$onglet = htmlentities(getpost_variable('onglet'), ENT_QUOTES | ENT_HTML401);

	if (!$onglet && is_admin($_SESSION['userlogin'])) {
		$onglet = 'general';
	}

	/*********************************/
	/*   COMPOSITION DES ONGLETS...  */
	/*********************************/

	$onglets = [];

	if (is_admin($_SESSION['userlogin'])) {
            $onglets['general'] = _('install_config_appli');
            $onglets['type_absence'] = _('install_config_types_abs');
            $onglets['mail'] = _('install_config_mail');
            $onglets['logs'] = _('config_logs');
        }

	/*********************************/
	/*   COMPOSITION DU HEADER...    */
	/*********************************/

	header_menu('', 'Libertempo : '._('admin_button_config_1'));


	/*********************************/
	/*   AFFICHAGE DES ONGLETS...  */
	/*********************************/


	echo '<div class="'.$onglet.' wrapper main-content">';

		if ($onglet == 'general') {
			include_once ROOT_PATH . 'config/configure.php';
		} else {
			include_once ROOT_PATH . 'config/config_'.$onglet.'.php';
		}

	echo '</div>';
