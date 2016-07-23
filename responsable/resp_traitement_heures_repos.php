<?php
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$repos = new \App\ProtoControllers\Responsable\Traitement\Repos();
echo $repos->getForm();
