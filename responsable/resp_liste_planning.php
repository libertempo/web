<?php

defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration(\includes\SQL);
if (!$config->canResponsablesAssociatePlanning()) {
    redirect(ROOT_PATH . 'deconnexion.php');
}
echo \responsable\Fonctions::getListePlanningModule();
