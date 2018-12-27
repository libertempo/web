<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';
include_once INCLUDE_PATH . 'session.php';

$config = new \App\Libraries\Configuration(\includes\SQL::singleton());

// SERVER
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
// GET / POST
$onglet = getpost_variable('onglet');

/*********************************/
/*   COMPOSITION DES ONGLETS...  */
/*********************************/

$onglets = array();

$onglets['liste_conge'] = _('user_conge');

if ($config->canUserEchangeRTT()) {
    $onglets['echange_jour_absence'] = _('user_onglet_echange_abs');
}

if ($config->isHeuresAutorise()) {
    $onglets['liste_heure_repos'] = _('user_liste_heure_repos');
    $onglets['liste_heure_additionnelle'] = _('user_liste_heure_additionnelle');
}

if ($config->canUserChangePassword()) {
    $onglets['changer_mot_de_passe'] = _('user_onglet_change_passwd');
}

if ( !isset($onglets[ $onglet ]) && !in_array($onglet, array('modif_demande','suppr_demande','modif_heure_repos', 'modif_heure_additionnelle', 'nouvelle_absence', 'ajout_heure_repos', 'ajout_heure_additionnelle'))) {
    $onglet = 'liste_conge';
}

/*********************************/
/*   COMPOSITION DU HEADER...    */
/*********************************/

header_menu('', 'Libertempo : ' . _('user'));

/*********************************/
/*   AFFICHAGE DU RECAP ...    */
/*********************************/

echo "<div class=\"wrapper\">\n";
echo '<h3>' . _('tableau_recap') . '</h3>';
echo affiche_tableau_bilan_conges_user($_SESSION['userlogin']);
echo "<hr/>\n";
echo "</div>\n";

/*********************************/
/*   AFFICHAGE DE L'ONGLET ...    */
/*********************************/

echo '<div class="' . $onglet . ' main-content">';
include ROOT_PATH . 'utilisateur/user_' . $onglet . '.php';
echo '</div>';

/*********************************/
/*   AFFICHAGE DU BOTTOM ...   */
/*********************************/

bottom();
