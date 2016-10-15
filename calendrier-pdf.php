<?php
define('ROOT_PATH', '');
include ROOT_PATH . 'define.php';
include_once ROOT_PATH .'fonctions_conges.php';
include_once INCLUDE_PATH .'fonction.php';


echo (new \App\ProtoControllers\Calendrier())->get();

return;
