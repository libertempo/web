<?php
include ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
$timeout=2 ; // refresh après maj.

$URL = "$PHP_SELF";

$update_plugin_table = "UPDATE conges_plugins SET p_is_active = '0'
  WHERE p_name='".$plugin."';";
$result_update_plugin_table = \includes\SQL::query($update_plugin_table);

$update_mail_table = "DELETE FROM `conges_mail` WHERE `mail_nom`='mail_cet_demande';";
$result_update_mail_table = \includes\SQL::query($update_mail_table);


?>
