<?php
defined('_PHP_CONGES') or die('Restricted access');
if (!$_SESSION['config']['gestion_heures']) {
    redirect(ROOT_PATH . 'responsable/resp_index.php');
}
$additionnelle = new \App\ProtoControllers\Responsable\Traitement\Additionnelle();
echo $additionnelle->getForm();
