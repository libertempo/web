<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$conges = new \App\ProtoControllers\Responsable\Traitement\Conge();
echo $conges->getForm();