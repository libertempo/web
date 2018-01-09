<?php

defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
if (!$config->canResponsablesAssociatePlanning()) {
    redirect(ROOT_PATH . 'deconnexion.php');
}

$message   = '';
$listPlanningId = \App\ProtoControllers\HautResponsable\Planning::getListPlanningId();
$titre = _('resp_liste_planning');
$lienModif = 'resp_index.php?onglet=modif_planning';
$isHr = false;
if (empty($listPlanningId)) {
    $listIdUsed = [];
    $plannings = [];
} else {
    $listIdUsed = \App\ProtoControllers\HautResponsable\Planning::getListPlanningUsed($listPlanningId);
    $injectableCreator = new \App\Libraries\InjectableCreator(\includes\SQL::singleton());
    $api = $injectableCreator->get(\App\Libraries\ApiClient::class);
    $plannings = $api->get('plannings', $_SESSION['token'])->data;
}

require_once VIEW_PATH . 'Planning/Liste.php';
