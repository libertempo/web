<?php
define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

if (!isset($_SESSION) || empty($_SESSION)) {
        die("{}");
}
$sql = \includes\SQL::singleton();
$config = new \App\Libraries\Configuration(\includes\SQL::singleton());

$nom = $_GET['nom'];

if (2 < strlen($nom)) {
    $injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
    $ldap = $injectableCreator->get(\App\Libraries\Ldap::class);
    echo $ldap->searchLdap($nom);
}
