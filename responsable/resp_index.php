<?php

define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';
defined('_PHP_CONGES') or die('Restricted access');

include_once ROOT_PATH . 'fonctions_conges.php';
include_once INCLUDE_PATH . 'fonction.php';
include_once INCLUDE_PATH . 'session.php';
include_once ROOT_PATH . 'fonctions_calcul.php';

$config = new \App\Libraries\Configuration(\includes\SQL::singleton());

// verif des droits du user à afficher la page
verif_droits_user("is_resp");

/*************************************/
// recup des parametres reçus :
// SERVER
$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
// GET / POST
$onglet = getpost_variable('onglet', "page_principale");

/*********************************/
/*   COMPOSITION DES ONGLETS...  */
/*********************************/

$onglets = array();

$onglets['page_principale'] = _('resp_menu_button_retour_main');

if ($config->canUserSaisieDemande()) {
    $nbbadgeConges = '';
    $DemandesConges = new \App\ProtoControllers\Responsable\Traitement\Conge;
    $nbdemandes = $DemandesConges->getNbDemandesATraiter($_SESSION['userlogin']);
    if (0 < $nbdemandes) {
        $nbbadgeConges = ' <span class="badge">' . $nbdemandes . '</span>';
    }
    $onglets['traitement_demandes'] = _('resp_menu_button_traite_demande') . $nbbadgeConges;
}

if ($config->isHeuresAutorise()) {
    $nbbadgeDem = '';
    $DemandesAdd = new \App\ProtoControllers\Responsable\Traitement\Additionnelle;
    $nbdemandes = $DemandesAdd->getNbDemandesATraiter($_SESSION['userlogin']);
    if (0 < $nbdemandes) {
        $nbbadgeDem = ' <span class="badge">' . $nbdemandes . '</span>';
    }
    $onglets['traitement_heures_additionnelles'] = _('resp_menu_button_traite_additionnelle') . $nbbadgeDem;

    $nbbadgeRep = '';
    $DemandesRep = new \App\ProtoControllers\Responsable\Traitement\Repos;
    $nbdemandes = $DemandesRep->getNbDemandesATraiter($_SESSION['userlogin']);
    if (0 < $nbdemandes) {
        $nbbadgeRep = ' <span class="badge">' . $nbdemandes . '</span>';
    }
    $onglets['traitement_heures_repos'] = _('resp_menu_button_traite_repos') . $nbbadgeRep;
}

if ($config->canResponsableAjouteConges()) {
    $onglets['ajout_conges'] = _('resp_ajout_conges_titre');
}

if ($config->canResponsablesAssociatePlanning()) {
    $onglets['liste_planning'] = _('resp_liste_planning');
}

if (!isset($onglets[$onglet]) && !in_array($onglet, array('traite_user', 'modif_planning'))) {
    $onglet = 'page_principale';
}

/*********************************/
/*   COMPOSITION DU HEADER...    */
/*********************************/

$add_css = '<style>#onglet_menu .onglet{ width: ' . (str_replace(',', '.', 100 / count($onglets))) . '% ;}</style>';
header_menu('', 'Libertempo : ' . _('divers_responsable_maj_1'), $add_css);

/*********************************/
/*   AFFICHAGE DE L'ONGLET ...    */
/*********************************/

/** initialisation des tableaux des types de conges/absences  **/
// recup du tableau des types de conges (seulement les conges)
$tab_type_cong = recup_tableau_types_conges();

// recup du tableau des types de conges exceptionnels (seulement les conges exceptionnels)
$tab_type_conges_exceptionnels = array();
if ($config->isCongesExceptionnelsActive()) {
    $tab_type_conges_exceptionnels = recup_tableau_types_conges_exceptionnels();
}

echo '<div class="' . $onglet . ' main-content">';
include_once ROOT_PATH . 'responsable/resp_' . $onglet . '.php';
echo '</div>';

/*********************************/
/*   AFFICHAGE DU BOTTOM ...   */
/*********************************/

bottom();
exit;
