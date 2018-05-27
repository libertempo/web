<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';

include_once ROOT_PATH .'fonctions_conges.php' ;
include_once INCLUDE_PATH .'fonction.php';
include_once INCLUDE_PATH .'session.php';

\edition\Fonctions::editPDFModule();
