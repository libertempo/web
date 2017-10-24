<?php
include_once ROOT_PATH . 'define.php';
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);
$URL = "$PHP_SELF";


$update_plugin_table = "UPDATE conges_plugins SET p_is_active = '1'
  WHERE p_name='".$plugin."';";
$result_update_plugin_table = \includes\SQL::query($update_plugin_table);


$select_mail_cet = "SELECT `mail_nom` FROM `conges_mail` WHERE `mail_nom`='mail_cet_demande';";
$result_select_mail_cet = \includes\SQL::query($select_mail_cet);
if($result_select_mail_cet->num_rows == 0)
    {
    $update_mail_table = "INSERT INTO `conges_mail` (`mail_nom`, `mail_subject`, `mail_body`) VALUES
    ('mail_cet_demande', 'APPLI CONGES - Demande stockage pour CET', ' __SENDER_NAME__ a solicité une demande d\'alimentation de son CET dans l\'application de gestion des congés.\r\n\r\nMerci de consulter votre application php_conges : __URL_ACCUEIL_CONGES__/\r\n\r\n-------------------------------------------------------------------------------------------------------\r\nCeci est un message automatique.');";
    $result_update_mail_table = \includes\SQL::query($update_mail_table);
    }


?>
