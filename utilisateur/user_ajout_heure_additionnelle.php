<?php
if (!$_SESSION['config']['gestion_heures']) {
    redirect(ROOT_PATH . 'utilisateur/user_index.php');
}
$additionnelle = new \App\ProtoControllers\Employe\Heure\Additionnelle();
echo $additionnelle->getForm();
