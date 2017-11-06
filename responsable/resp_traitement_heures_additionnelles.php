<?php
defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
if (!$config->isHeuresAutorise()) {
    redirect(ROOT_PATH . 'responsable/resp_index.php');
}
$additionnelle = new \App\ProtoControllers\Responsable\Traitement\Additionnelle();

$errorsLst = [];
$notice = '';
if (!empty($_POST) && 0 >= (int) $additionnelle->post($_POST, $notice, $errorsLst)) {
    //
}

$titre = _('traitement_heure_additionnelle_titre');

$demandesResp = $additionnelle->getDemandesResponsable($_SESSION['userlogin']);
$formResponsable = (!empty($demandesResp))
    ? $additionnelle->getFormDemandes($demandesResp)
    : [];

$demandesGrandResp = $additionnelle->getDemandesGrandResponsable($_SESSION['userlogin']);
$formGrandResponsable = (!empty($demandesGrandResp))
    ? $additionnelle->getFormDemandes($demandesGrandResp)
    : [];

require_once VIEW_PATH . 'Responsable/Validation/Liste.php';
