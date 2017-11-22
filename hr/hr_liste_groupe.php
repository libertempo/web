<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );
if (getpost_variable('notice') !== ""){
    $notice = getpost_variable('notice');
    if("insert" === $notice){
        $message = _('Groupe créé');
    } elseif ("update" === $notice) {
        $message = _('Groupe modifié');
    }
} else {
    $message = "";
}
$gestionGroupes = new \App\ProtoControllers\Groupe\Gestion();
echo $gestionGroupes->getFormListGroupe($message);