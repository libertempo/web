<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$id = (int) getpost_variable('group');

if (0 >= $id) {
    redirect(ROOT_PATH . 'deconnexion.php');
}

$gestionGroupes = new \App\ProtoControllers\Groupe\Gestion();
echo $gestionGroupes->getForm($id);
