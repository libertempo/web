<?php
defined('ROOT_PATH') or define('ROOT_PATH', '../');
defined('INCLUDE_PATH') or define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
include_once INCLUDE_PATH . 'session.php';

// verif des droits du user à afficher la page
verif_droits_user('is_admin');

$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
/*************************************/
// recup des parametres reçus :
// SERVER
$PHP_SELF = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL);

/*********************************/
/*   COMPOSITION DU HEADER...    */
/*********************************/

header_menu('', 'Libertempo : '._('button_admin_mode'),'');


/*********************************/
/*   AFFICHAGE DE L'ONGLET ...    */
/*********************************/


/** initialisation des tableaux des types de conges/absences  **/
// recup du tableau des types de conges (seulement les conges)
$tab_type_cong=recup_tableau_types_conges();

// recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
$tab_type_conges_exceptionnels=recup_tableau_types_conges_exceptionnels();

echo '<div class="wrapper main-content">';
    include_once ROOT_PATH . 'admin/admin_db_sauve.php';
echo '</div>';

/*********************************/
/*   AFFICHAGE DU BOTTOM ...   */
/*********************************/

bottom();
exit;
