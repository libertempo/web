<?php
/*************************************************************************************************
Libertempo : Gestion Interactive des Congés
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

define('ROOT_PATH', '../');
require ROOT_PATH . 'define.php';

/*******************************************************************/
// SCRIPT DE MIGRATION DE LA VERSION 1.5.0 vers 1.6.0
/*******************************************************************/
include ROOT_PATH .'fonctions_conges.php' ;
include INCLUDE_PATH .'fonction.php';

$PHP_SELF=$_SERVER['PHP_SELF'];

$DEBUG=FALSE;
//$DEBUG=TRUE;

/*
 A FAIRE :

- supprimer les fichiers INSTALL.txt, README.txt, TODO.txt et Licence.txt de la racine (ils sont maintenant dans le répertoire /docs)


*/


$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$lang = (isset($_GET['lang']) ? $_GET['lang'] : (isset($_POST['lang']) ? $_POST['lang'] : "")) ;

    $old_conf = array(
        'bgcolor',
        'bgimage',
        'img_login',
        'lien_img_login',
        'php_conges_authldap_include_path',
        'php_conges_cas_include_path',
        'php_conges_fpdf_include_path',
        'php_conges_phpmailer_include_path',
        'texte_img_login',
        'texte_page_login',
    );

    $sql_delete_1 = "DELETE FROM conges_config WHERE conf_nom IN ('". implode("' , '", $old_conf) . "');";
    $result_delete_1 = \includes\SQL::query($sql_delete_1)  ;

    $sql_alter_1=" ALTER TABLE  `conges_users` ADD  `u_is_hr` ENUM( 'Y','N' ) NOT NULL DEFAULT 'N' AFTER `u_is_admin`;";
    $result_alter_1 = \includes\SQL::query($sql_alter_1)  ;

    $sql_alter_2=" ALTER TABLE  `conges_users` ADD  `u_is_active` ENUM( 'Y','N' ) NOT NULL DEFAULT 'Y' AFTER `u_is_hr`;";
    $result_alter_2 = \includes\SQL::query($sql_alter_2)  ;

    $sql_insert_1="INSERT INTO  `conges_config` (`conf_nom` ,`conf_valeur` ,`conf_groupe` ,`conf_type` ,`conf_commentaire`) VALUES ('print_disable_users',  'FALSE',  '06_Responsable',  'Boolean',  'config_comment_print_disable_users');";
    $result_insert_1= \includes\SQL::query($sql_insert_1)  ;

    $sql_update_1="UPDATE  `conges_config` SET  `conf_valeur` =  'style.css' WHERE  `conges_config`.`conf_nom` =  'stylesheet_file';";
    $result_update_1 = \includes\SQL::query($sql_update_1)  ;

    $sql_insert_1="INSERT INTO  `conges_config` (`conf_nom` ,`conf_valeur` ,`conf_groupe` ,`conf_type` ,`conf_commentaire`) VALUES ('affiche_jours_current_month_calendrier',  'FALSE',  '13_Divers',  'Boolean',  'config_comment_affiche_jours_current_month_calendrier');";
    $result_insert_1= \includes\SQL::query($sql_insert_1)  ;


    // on renvoit à la page mise_a_jour.php (là d'ou on vient)
    echo "<a href=\"mise_a_jour.php?etape=4&version=$version&lang=$lang\">upgrade_from_v1.5.0  OK</a><br>\n";
