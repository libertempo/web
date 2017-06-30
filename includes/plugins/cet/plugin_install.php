<?php
include_once ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
$timeout=2 ; // refresh après maj.

$URL = "$PHP_SELF";
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
