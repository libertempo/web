<?php
$config = new \App\Libraries\Configuration();
if (!$config->isHeuresAutorise()) {
    redirect(ROOT_PATH . 'utilisateur/user_index.php');
}
$additionnelle = new \App\ProtoControllers\Employe\Heure\Additionnelle();
echo $additionnelle->getForm();
