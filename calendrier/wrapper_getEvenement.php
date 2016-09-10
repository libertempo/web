<?php
define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

/* Faut vraiment qu'on enlève le passage de la session par paramètre, ça en devient ridicule... */
$session = '';
if (isset($_GET['session'])) {
    $session = $_GET['session'];
} elseif (isset($_POST['session'])) {
    $session = $_POST['session'];
} else {
    $session = session_id();
}

session_name($session);
session_id($session);
session_start();

$evenement = new \App\ProtoControllers\Ajax\Evenement();
echo $evenement->getListe($_GET);
