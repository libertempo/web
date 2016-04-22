<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
Copyright (C) 2015 (Wouldsmina)
Copyright (C) 2015 (Prytoegrian)
Copyright (C) 2005 (cedric chauvineau)

Ce programme est libre, vous pouvez le redistribuer et/ou le modifier selon les
termes de la Licence Publique Générale GNU publiée par la Free Software Foundation.
Ce programme est distribué car potentiellement utile, mais SANS AUCUNE GARANTIE,
ni explicite ni implicite, y compris les garanties de commercialisation ou d'adaptation
dans un but spécifique. Reportez-vous à la Licence Publique Générale GNU pour plus de détails.
Vous devez avoir reçu une copie de la Licence Publique Générale GNU en même temps
que ce programme ; si ce n'est pas le cas, écrivez à la Free Software Foundation,
Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, États-Unis.
*************************************************************************************************
This program is free software; you can redistribute it and/or modify it under the terms
of the GNU General Public License as published by the Free Software Foundation; either
version 2 of the License, or any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*************************************************************************************************/

define('_PHP_CONGES', 1);
define('ROOT_PATH', '../');
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.8 vers 1.8.1
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';

$PHP_SELF=$_SERVER['PHP_SELF'];

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;

//suppression des droits de conges
$del_conges_acl = "DELETE FROM conges_groupe_resp WHERE gr_login = 'conges';";
$res_del_conges_acl = \includes\SQL::query($del_conges_acl);

$del_conges_acl = "DELETE FROM conges_groupe_grd_resp WHERE ggr_login = 'conges';";
$res_del_conges_acl=\includes\SQL::query($del_conges_acl);

//modifications des users ayant comme responsable conges
$upd_user_resp = "UPDATE conges_users SET u_resp_login = NULL WHERE u_login = 'conges';";
$res_upd_user_resp=\includes\SQL::query($upd_user_resp);

//suppression des artt de conges
$del_conges_artt = "DELETE FROM conges_artt WHERE gr_login = 'conges';";
$res_del_conges_acl = \includes\SQL::query($del_conges_acl);

//suppression du user conges
$del_conges_usr="DELETE FROM conges_users WHERE u_login = 'conges';";
$res_del_conges_acl=\includes\SQL::query($del_conges_acl);


// on renvoit à la page mise_a_jour.php (là d'ou on vient)
echo "<a href=\"mise_a_jour.php?etape=3&version=$version&lang=$lang\">upgrade_from_v1.8  OK</a><br>\n";
