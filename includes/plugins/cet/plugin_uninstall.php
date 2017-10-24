<?php
include_once ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$timeout=2 ; // refresh après maj.

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

$URL = "$PHP_SELF";

echo "<META HTTP-EQUIV=REFRESH CONTENT=\"$timeout; URL=$URL\">";

$delete_table_plugin_cet_query = "DROP TABLE IF EXISTS `conges_plugin_cet`;";


$result_delete_table_plugin = \includes\SQL::query($delete_table_plugin_cet_query);

$update_plugin_table = "DELETE FROM conges_plugins WHERE p_name='".$plugin."';";
//$update_plugin_table = "UPDATE conges_plugins SET p_is_install='0' WHERE p_name='$plugin';"
$result_update_plugin_table = \includes\SQL::query($update_plugin_table);

?>
