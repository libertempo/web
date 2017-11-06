<?php
defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
if (!$config->isHeuresAutorise()) {
    redirect(ROOT_PATH . 'responsable/resp_index.php');
}
$repos = new \App\ProtoControllers\Responsable\Traitement\Repos();

$errorsLst = [];
$notice = '';
if (!empty($_POST) && 0 >= (int) $repos->post($_POST, $notice, $errorsLst)) {
    //
}

$titre = _('traitement_heure_repos_titre');

$demandesResp = $repos->getDemandesResponsable($_SESSION['userlogin']);
$formResponsable = (!empty($demandesResp))
    ? $repos->getFormDemandes($demandesResp)
    : [];

$demandesGrandResp = $repos->getDemandesGrandResponsable($_SESSION['userlogin']);
$formGrandResponsable = (!empty($demandesGrandResp))
    ? $repos->getFormDemandes($demandesGrandResp)
    : [];

require_once VIEW_PATH . 'Responsable/Validation/Liste.php';
