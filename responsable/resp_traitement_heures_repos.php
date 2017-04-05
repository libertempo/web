<?php
defined('_PHP_CONGES') or die('Restricted access');
if (!$_SESSION['config']['gestion_heures']) {
    $session = (isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()));
    redirect(ROOT_PATH . 'responsable/resp_index.php?session=' . $session);
}
$repos = new \App\ProtoControllers\Responsable\Traitement\Repos();
echo $repos->getForm();
