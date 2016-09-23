<?php
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$additionnelle = new \App\ProtoControllers\Responsable\Traitement\Additionnelle();
echo $additionnelle->getForm();
