<?php

define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

include INCLUDE_PATH .'fonction.php';
include ROOT_PATH .'fonctions_conges.php'; // for init_config_tab()

$with_groups    = ($_SESSION['config']['gestion_groupes'] AND isset($_GET['with_groups']) AND $_GET['with_groups']);
$with_user_resp = (isset($_GET['with_user_resp']) AND $_GET['with_user_resp']);
$only_validated = (isset($_GET['only_validated']) AND $_GET['only_validated']);

\export\Fonctions::exportICSModule($with_groups, $with_user_resp, $only_validated);
