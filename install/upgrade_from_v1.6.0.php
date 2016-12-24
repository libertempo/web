<?php

define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.6.0 vers 1.7.0
/*******************************************************************/
include_once ROOT_PATH .'fonctions_conges.php' ;
include_once INCLUDE_PATH .'fonction.php';

$PHP_SELF=$_SERVER['PHP_SELF'];

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$version = htmlentities($version, ENT_QUOTES | ENT_HTML401);
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;
$lang = htmlentities($lang, ENT_QUOTES | ENT_HTML401);

//étape 1 création de la table de gestion des plugins
\install\Fonctions::e1_create_table_plugins();

// on renvoit à la page mise_a_jour.php (là d'ou on vient)
echo "<a href=\"mise_a_jour.php?etape=2&version=$version&lang=$lang\">upgrade_from_v1.6.0  OK</a><br>\n";
