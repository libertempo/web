<?php
defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
if (!$config->isHeuresAutorise()) {
    redirect(ROOT_PATH . 'utilisateur/user_index.php');
}
use \App\Models\AHeure;
$repos = new \App\ProtoControllers\Employe\Heure\Repos();

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
    if (0 < (int) $repos->postHtmlCommon($_POST, $errorsLst, $notice)) {
        log_action(0, '', '', 'Récupération de l\'heure de repos ' . $_POST['id_heure']);
        redirect(ROOT_PATH . 'utilisateur/user_index.php?onglet=liste_heure_repos', false);
    }
}
$champsRecherche = (!empty($_POST) && isSearch($_POST))
    ? $repos->transformChampsRecherche($_POST)
    : [];
$params = $champsRecherche + [
    'login' => $_SESSION['userlogin'],
];

$canUserSaisi = $config->canUserSaisieDemande() || $config->canUserSaisieMission();
$urlSaisie = 'utilisateur/user_index.php?onglet=ajout_heure_repos';
$texteSaisie = _('divers_ajout_heure_repos');
$titre = _('user_liste_heure_repos_titre');

$listId = $repos->getListeId($params);
$dataHeures = [];
if (!empty($listId)) {
    $listeRepos = $repos->getListeSQL($listId);
    foreach ($listeRepos as $repos) {
        $data = new \stdClass;
        $dataHeures[] = $data;
        $data->jour = date('d/m/Y', $repos['debut']);
        $data->debut = date('H\:i', $repos['debut']);
        $data->fin = date('H\:i', $repos['fin']);
        $data->duree = \App\Helpers\Formatter::Timestamp2Duree($repos['duree']);
        $data->statut = AHeure::statusText($repos['statut']);
        $data->comment = \includes\SQL::quote($repos['comment']);
        $data->isModifiable = AHeure::STATUT_DEMANDE == $repos['statut'];
        $data->urlModification = 'user_index.php?onglet=modif_heure_repos&id=' . $repos['id_heure'];
        $data->idHeure = $repos['id_heure'];
    }
}

require_once VIEW_PATH . 'Employe/Heure/Liste.php';
