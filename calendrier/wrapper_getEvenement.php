<?php
define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';
require_once ROOT_PATH . 'fonctions_conges.php';

/* Faut vraiment qu'on enlève le passage de la session par paramètre, ça en devient ridicule... */
$session = '';
if (isset($_GET['session'])) {
    $session = $_GET['session'];
    unset($_GET['session']);
} elseif (isset($_POST['session'])) {
    $session = $_POST['session'];
} else {
    $session = session_id();
}

/* TODO après les tests de dev : si session vide :: exit */

session_name($session);
session_id($session);
session_start();

$_SESSION['config'] = init_config_tab();

$evenement = new \App\ProtoControllers\Ajax\Evenement();
echo $evenement->getListe($_GET, $_SESSION['u_login']);
