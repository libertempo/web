<?php
define('ROOT_PATH', '');
require_once ROOT_PATH . 'define.php';

$session=(isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()) ) ;

include_once ROOT_PATH .'fonctions_conges.php';
include_once INCLUDE_PATH .'fonction.php';
header_menu('', 'Libertempo : '._('calendrier_titre'));

echo (new \App\ProtoControllers\Calendrier())->get();

bottom();
