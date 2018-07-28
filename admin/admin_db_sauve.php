<?php
defined('ROOT_PATH') or define('ROOT_PATH', '../');
defined('INCLUDE_PATH') or define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
if (!defined('_PHP_CONGES')) {
    require INCLUDE_PATH . 'define.php';
}
verif_droits_user('is_admin');


$choix_action    = getpost_variable('choix_action');
$type_sauvegarde = getpost_variable('type_sauvegarde');
$commit          = getpost_variable('commit');
if($choix_action=="sauvegarde" && isset($type_sauvegarde)
    && $type_sauvegarde != "" && isset($commit) && $commit != ""
) {
    \admin\Fonctions::commit_sauvegarde($type_sauvegarde);
} else {
    \admin\Fonctions::saveRestoreModule();
}
