<?php

defined( '_PHP_CONGES' ) or die( 'Restricted access' );
if (getpost_variable('notice') !== "") {
    $notice = getpost_variable('notice');
    if ("insert" === $notice) {
        $message = _('Groupe créé');
    } elseif ("update" === $notice) {
        $message = _('Groupe modifié');
    }
} else {
    $message = "";
}
$gestionGroupes = new \App\ProtoControllers\Groupe\Gestion();
$errorsLst = [];
$errors = null;

if (!empty($_POST)) {
    if (0 >= (int) $gestionGroupes->postHtmlCommon($_POST, $errorsLst)) {
        if (!empty($errorsLst)) {
            foreach ($errorsLst as $key => $value) {
                if (is_array($value)) {
                    $value = implode(' / ', $value);
                }
                $errors[$key] = $value;
            }
        }
    } else {
        $message = _('Groupe supprimé');
    }
}

$config = new \App\Libraries\Configuration(\includes\SQL::singleton());
$isDoubleValidationActive = $config->isDoubleValidationActive();
$injectableCreator = new \App\Libraries\InjectableCreator($sql, $config);
$api = $injectableCreator->get(\App\Libraries\ApiClient::class);
$groupes = $api->get('groupe', $_SESSION['token'])['data'];

require_once VIEW_PATH . 'Groupe/Liste.php';
