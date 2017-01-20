<?php

defined('_PHP_CONGES') or die('Restricted access');
if (!\App\ProtoControllers\Responsable::canAssociatePLanning()) {
    redirect(ROOT_PATH . 'deconnexion.php');
}
echo \responsable\Fonctions::getListePlanningModule();
