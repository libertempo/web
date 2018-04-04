<?php
defined( '_PHP_CONGES' ) or die( 'Restricted access' );

$choix_action    = getpost_variable('choix_action');
$type_sauvegarde = getpost_variable('type_sauvegarde');
$commit          = getpost_variable('commit');
if($choix_action=="sauvegarde" && isset($type_sauvegarde)
    && $type_sauvegarde != "" && isset($commit) && $commit != ""
) {
    commit_sauvegarde($type_sauvegarde);
}

function commit_sauvegarde($type_sauvegarde)
{
    header("Pragma: no-cache");
    header("Content-Type: text/x-delimtext; name=\"php_conges_".$type_sauvegarde.".sql\"");
    header("Content-disposition: attachment; filename=php_conges_".$type_sauvegarde.".sql");

    echo \admin\Fonctions::getDataFile($type_sauvegarde);
}
// verif des droits du user à afficher la page
verif_droits_user('is_admin');
echo '<div class="main-content">';

\admin\Fonctions::saveRestoreModule();

echo '</div>';
