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

$sql = \includes\SQL::singleton();
$sql->getPdoObj()->begin_transaction();

$del_config_db = "DELETE FROM conges_config WHERE conf_nom = 'duree_session' LIMIT 1;";
$sql->query($del_config_db);

$alterUserSeeAll = "ALTER TABLE `conges_users` DROP `u_see_all`;";
$sql->query($alterUserSeeAll);

$alterUserResp = "ALTER TABLE `conges_users` DROP `u_resp_login`;";
$sql->query($alterUserResp);

$sql->getPdoObj()->commit();

// on renvoit à la page mise_a_jour.php (là d'ou on vient)
printf("Migration depuis v1.10 effectuée. <a href=\"mise_a_jour.php?etape=2&version=%s\">Continuer.</a><br>\n", $version);
