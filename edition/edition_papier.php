<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';
require_once INCLUDE_PATH .'session.php';

echo \edition\Fonctions::editPapierModule();
bottom();
