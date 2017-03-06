<?php

defined('_PHP_CONGES') or die('Restricted access');

$session = (isset($_GET['session']) ? $_GET['session'] : ((isset($_POST['session'])) ? $_POST['session'] : session_id()));

if (file_exists(CONFIG_PATH . 'config_ldap.php')) {
    include CONFIG_PATH . 'config_ldap.php';
}

echo \hr\Fonctions::pageJoursChomesModule($session);
