<?php

defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
if (!$config->canResponsablesAssociatePlanning()) {
    redirect(ROOT_PATH . 'deconnexion');
}

$message   = '';
$titre = _('resp_liste_planning');
$lienModif = 'resp_index.php?onglet=modif_planning';
$isHr = false;
$listPlanningId = \App\ProtoControllers\HautResponsable\Planning::getListPlanningId();
$listIdUsed = \App\ProtoControllers\HautResponsable\Planning::getListPlanningUsed($listPlanningId);

require_once VIEW_PATH . 'Planning/Liste.php';
