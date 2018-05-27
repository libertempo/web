<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');

require_once INCLUDE_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

\export\Fonctions::exportICSModule();
