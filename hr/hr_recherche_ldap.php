<?php
define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

if(!isset($_SESSION) || empty($_SESSION)){
        die("{}");
}

$nom =$_GET['nom'];

if(2 < strlen($nom)){
    $ldap = new \App\Libraries\Ldap();
    echo $ldap->searchLdap($nom);
}