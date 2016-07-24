<?php
define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

$evenement = new \App\ProtoControllers\Ajax\Evenement();
echo $evenement->get();
