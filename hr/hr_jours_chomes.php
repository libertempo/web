<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );

if (file_exists(CONFIG_PATH .'config_ldap.php'))
    include CONFIG_PATH .'config_ldap.php';

echo \hr\Fonctions::pageJoursChomesModule();
