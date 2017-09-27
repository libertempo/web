<?php

defined('_PHP_CONGES') or die('Restricted access');
if (!\App\ProtoControllers\Responsable::canAssociatePLanning()) {
    redirect(ROOT_PATH . 'deconnexion.php');
}

$message   = '';
$listPlanningId = \App\ProtoControllers\HautResponsable\Planning::getListPlanningId();
$ajoutPlanning = '';
$titre = _('resp_liste_planning');
$lienModif = 'resp_index.php?onglet=modif_planning';
$canDelete = false;
if (empty($listPlanningId)) {
    $listIdUsed = [];
    $plannings = [];
} else {
    $listIdUsed = \App\ProtoControllers\HautResponsable\Planning::getListPlanningUsed($listPlanningId);
    $injectableCreator = new \App\Libraries\InjectableCreator(\includes\SQL::singleton());
    $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
    $plannings = $api->get('plannings', $_SESSION['token']);
}

require_once VIEW_PATH . 'Planning/Liste.php';
