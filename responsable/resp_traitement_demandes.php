<?php
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$conges = new \App\ProtoControllers\Responsable\Traitement\Conge();

$errorsLst = [];
$notice = '';
if (!empty($_POST) && 0 >= (int) $conges->post($_POST, $notice, $errorsLst)) {
    // ...
}

$demandesResp = $conges->getDemandesResponsable($_SESSION['userlogin']);
$formResponsable = (!empty($demandesResp))
    ? $conges->getFormDemandes($demandesResp)
    : [];
$demandesGrandResp = $conges->getDemandesGrandResponsable($_SESSION['userlogin']);
$formGrandResponsable = (!empty($demandesGrandResp))
    ? $conges->getFormDemandes($demandesGrandResp)
    : [];
$demandesRespDelegation = $this->getDemandesResponsableAbsent($_SESSION['userlogin']);
$formDelegation = (!empty($demandesRespDelegation))
    ? $conges->getFormDemandes($demandesRespDelegation)
    : [];

require_once VIEW_PATH . 'Responsable/Conge/Validation.php';
