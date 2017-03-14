<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );
$gestionGroupes = new \App\ProtoControllers\Groupe\Gestion();
echo $gestionGroupes->getForm();