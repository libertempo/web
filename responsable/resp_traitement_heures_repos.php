<?php
defined('_PHP_CONGES') or die('Restricted access');
if (!$_SESSION['config']['gestion_heures']) {
    redirect(ROOT_PATH . 'responsable/resp_index.php');
}
$repos = new \App\ProtoControllers\Responsable\Traitement\Repos();
echo $repos->getForm();
