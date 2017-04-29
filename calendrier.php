<?php
define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';

include_once ROOT_PATH .'fonctions_conges.php';
include_once INCLUDE_PATH .'fonction.php';
header_menu('', 'Libertempo : '._('calendrier_titre'));

echo (new \App\ProtoControllers\Calendrier())->getAnother();

echo (new \App\ProtoControllers\Calendrier())->get();

bottom();
