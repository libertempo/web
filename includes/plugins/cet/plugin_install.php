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

include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$DEBUG=FALSE;

$PHP_SELF=$_SERVER['PHP_SELF'];
$timeout=2 ; // refresh après maj.

if($session=="")
    $URL = "$PHP_SELF";
else
    $URL = "$PHP_SELF?session=$session";
echo "<META HTTP-EQUIV=REFRESH CONTENT=\"$timeout; URL=$URL\">";


$create_table_plugin_cet_query = "CREATE TABLE IF NOT EXISTS `conges_plugin_cet` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pc_jours_demandes` decimal(5,2) DEFAULT NULL COMMENT 'Number of days requested to supply the CET',
  `pc_requested_date` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pc_comments` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL,
  `pc_u_login` varbinary(99) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `pc_u_login` (`pc_u_login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;";

$result_create_table_plugin = \includes\SQL::query($create_table_plugin_cet_query);

$get_plugin_from_db = "SELECT * FROM conges_plugins WHERE p_name='".$plugin."';";
$result_get_plugin = \includes\SQL::query($get_plugin_from_db);

if($result_get_plugin->num_rows == 0)
    {
    $update_plugin_table = "INSERT INTO conges_plugins(p_name,p_is_install) VALUES ('".$plugin."','1');";
    $result_update_plugin_table = \includes\SQL::query($update_plugin_table);
    }
else{
    $update_plugin_table = "UPDATE conges_plugins SET p_is_install = '1'
      WHERE p_name='".$plugin."';";
    $result_update_plugin_table = \includes\SQL::query($update_plugin_table);
    }


?>
