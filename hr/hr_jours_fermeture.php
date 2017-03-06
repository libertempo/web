<?php

define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

$session = (isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()));

if (file_exists(CONFIG_PATH . 'config_ldap.php')) {
    include_once CONFIG_PATH . 'config_ldap.php';
}

include_once ROOT_PATH . 'fonctions_conges.php';
include_once INCLUDE_PATH . 'fonction.php';
include_once INCLUDE_PATH . 'session.php';
include_once ROOT_PATH . 'fonctions_calcul.php';

echo \hr\Fonctions::pageJoursFermetureModule($session);
bottom();
