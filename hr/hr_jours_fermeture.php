<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';

if (file_exists(CONFIG_PATH .'config_ldap.php')) {
    include_once CONFIG_PATH .'config_ldap.php';
}
include_once INCLUDE_PATH .'session.php';

echo \hr\Fonctions::pageJoursFermetureModule();
bottom();
