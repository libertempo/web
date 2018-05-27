<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');

require_once INCLUDE_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

include INCLUDE_PATH .'fonction.php';
include ROOT_PATH .'fonctions_conges.php'; // for init_config_tab()
\export\Fonctions::exportICSModule();
