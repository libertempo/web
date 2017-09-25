<?php

define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.10 vers 1.11
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$version = htmlentities($version, ENT_QUOTES | ENT_HTML401);
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;
$lang = htmlentities($lang, ENT_QUOTES | ENT_HTML401);
if (!in_array($lang, ['fr_FR', 'en_US', 'es_ES'], true)) {
    $lang = '';
}

$sql = \includes\SQL::singleton();
$sql->getPdoObj()->begin_transaction();

$del_config_db="DELETE FROM conges_config WHERE conf_nom = 'duree_session' LIMIT 1;";
$sql->query($del_config_db);

$sql->getPdoObj()->commit();

// on renvoit à la page mise_a_jour.php (là d'ou on vient)
echo "Migration depuis v1.9 effectuée. <a href=\"mise_a_jour.php?etape=2&version=$version&lang=$lang\">Continuer.</a><br>\n";
