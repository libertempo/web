<?php

define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

if (file_exists(CONFIG_PATH .'config_ldap.php')) {
    include_once CONFIG_PATH .'config_ldap.php';
}


include_once ROOT_PATH .'fonctions_conges.php' ;
include_once INCLUDE_PATH .'fonction.php';
include_once INCLUDE_PATH .'session.php';
include_once ROOT_PATH .'fonctions_calcul.php';

echo \hr\Fonctions::pageJoursFermetureModule();
bottom();
