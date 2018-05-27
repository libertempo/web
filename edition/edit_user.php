<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';
include_once INCLUDE_PATH .'session.php';

echo \edition\Fonctions::editUserModule();
bottom();
