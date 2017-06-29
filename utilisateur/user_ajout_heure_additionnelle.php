<?php
$config = new \App\Libraries\Configuration();
if (!$config->isHeuresAutorise()) {
    $session = (isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()));
    redirect(ROOT_PATH . 'utilisateur/user_index.php?session=' . $session);
}
$additionnelle = new \App\ProtoControllers\Employe\Heure\Additionnelle();
echo $additionnelle->getForm();
