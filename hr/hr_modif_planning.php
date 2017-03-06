<?php

defined('_PHP_CONGES') or die('Restricted access');
$id = (int) getpost_variable('id');
if (0 >= $id || !\App\ProtoControllers\HautResponsable\Planning::isVisible($id)) {
    redirect(ROOT_PATH . 'deconnexion.php');
}
echo \hr\Fonctions::getFormPlanningModule($id);
