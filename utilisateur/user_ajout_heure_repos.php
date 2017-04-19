<?php
$config = new \App\Libraries\Configuration();
if (!$config->isHeuresAutorise()) {
    redirect(ROOT_PATH . 'utilisateur/user_index.php');
}
$repos = new \App\ProtoControllers\Employe\Heure\Repos();
echo $repos->getForm();
