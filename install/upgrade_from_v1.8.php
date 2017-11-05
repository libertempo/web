<?php

define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.8 vers 1.8.1
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
//suppression des droits de conges
$del_conges_acl = "DELETE FROM conges_groupe_resp WHERE gr_login = 'conges';";
$res_del_conges_acl = \includes\SQL::query($del_conges_acl);

$del_conges_acl = "DELETE FROM conges_groupe_grd_resp WHERE ggr_login = 'conges';";
$res_del_conges_acl=\includes\SQL::query($del_conges_acl);

//modifications des users ayant comme responsable conges
$upd_user_resp = "UPDATE conges_users SET u_resp_login = NULL WHERE u_login = 'conges';";
$res_upd_user_resp=\includes\SQL::query($upd_user_resp);

//suppression des artt de conges
$del_conges_artt = "DELETE FROM conges_artt WHERE a_login = 'conges';";
$res_del_conges_artt = \includes\SQL::query($del_conges_artt);

//suppression du user conges
$del_conges_usr="DELETE FROM conges_users WHERE u_login = 'conges';";
$res_del_conges_usr=\includes\SQL::query($del_conges_usr);

// on renvoit à la page mise_a_jour.php (là d'ou on vient)
echo "Migration depuis v1.8 effectuée. <a href=\"mise_a_jour.php?etape=2&version=$version&lang=$lang\">Continuer.</a><br>\n";
