<?php declare(strict_types = 1);

defined('_PHP_CONGES') or die('Restricted access');
$planningId = (int) getpost_variable('id');
if (0 >= $planningId || !\App\ProtoControllers\HautResponsable\Planning::isVisible($planningId)) {
    redirect(ROOT_PATH . 'deconnexion');
}

$titre = _('hr_modif_planning_titre');

require_once 'hr_edition_planning.php';
