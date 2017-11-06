<?php
defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());

if (!$config->isHeuresAutorise()) {
    redirect(ROOT_PATH . 'utilisateur/user_index.php');
}
use \App\Models\AHeure;
$additionnelle = new \App\ProtoControllers\Employe\Heure\Additionnelle();

/**
 * Y-a-t-il une recherche dans l'avion ?
 *
 * @param array $post
 *
 * @return bool
 */
function isSearch(array $post)
{
    return !empty($post['search']);
}

$errorsLst = [];
$notice    = '';
if (!empty($_POST) && !isSearch($_POST)) {
    if (0 < (int) $additionnelle->postHtmlCommon($_POST, $errorsLst, $notice)) {
        log_action(0, '', '', 'Récupération de l\'heure additionnelle ' . $_POST['id_heure']);
        redirect(ROOT_PATH . 'utilisateur/user_index.php?onglet=liste_heure_additionnelle', false);
    }
}
$champsRecherche = (!empty($_POST) && isSearch($_POST))
    ? $additionnelle->transformChampsRecherche($_POST)
    : [];
$params = $champsRecherche + [
    'login' => $_SESSION['userlogin'],
];

$canUserSaisi = $config->canUserSaisieDemande() || $config->canUserSaisieMission();
$urlSaisie = 'utilisateur/user_index.php?onglet=ajout_heure_additionnelle';
$texteSaisie = _('divers_ajout_heure_additionnelle');
$titre = _('user_liste_heure_additionnelle_titre');

$listId = $additionnelle->getListeId($params);
$dataHeures = [];
if (!empty($listId)) {
    $listeAdditionelle = $additionnelle->getListeSQL($listId);
    foreach ($listeAdditionelle as $additionnelle) {
        $data = new \stdClass;
        $dataHeures[] = $data;
        $data->jour = date('d/m/Y', $additionnelle['debut']);
        $data->debut = date('H\:i', $additionnelle['debut']);
        $data->fin = date('H\:i', $additionnelle['fin']);
        $data->duree = \App\Helpers\Formatter::Timestamp2Duree($additionnelle['duree']);
        $data->statut = AHeure::statusText($additionnelle['statut']);
        $data->comment = \includes\SQL::quote($additionnelle['comment']);
        $data->isModifiable = AHeure::STATUT_DEMANDE == $additionnelle['statut'];
        $data->urlModification = 'user_index.php?onglet=modif_heure_additionnelle&id=' . $additionnelle['id_heure'];
        $data->idHeure = $additionnelle['id_heure'];
    }
}

require_once VIEW_PATH . 'Employe/Heure/Liste.php';
