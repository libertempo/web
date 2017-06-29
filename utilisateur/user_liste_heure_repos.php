<?php

defined('_PHP_CONGES') or die('Restricted access');
$config = new \App\Libraries\Configuration();
if (!$config->isHeuresAutorise()) {
    $session = (isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()));
    redirect(ROOT_PATH . 'utilisateur/user_index.php?session=' . $session);
}
$repos = new \App\ProtoControllers\Employe\Heure\Repos();
echo $repos->getListe();
