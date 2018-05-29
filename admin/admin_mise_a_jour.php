<?php declare(strict_types = 1);

require_once ROOT_PATH . 'version.php' ;

/**
 * Effectue la mise à jour et retourne le numéro de dernier patch installé
 */
function miseAJour(string $installed_version, string $config_php_conges_version) : string
{
    // Avant tout, une petite protection…
    \admin\Fonctions::sauvegardeAsFile($installed_version, 'end');

    $versionDerniereMAJ = self::getVersionDerniereMiseAJour();
    list($major, $minor, _) = explode('.', $versionDerniereMAJ);
    // @TODO: s'assurer que les fichiers sont lus dans le bons sens
    // ie. 1.9 avant 1.10
    foreach (glob(MAJ_PATH . '/' . $major . '.' . $minor . '*.sql') as $filename) {
        $currentPatch = basename($filename, '.sql');
        if (version_compare($currentPatch, $versionDerniereMAJ, '>')) {
            execute_sql_file($filename);
        }
    }

    $comment_log = _('install_maj_titre_2')." (version $installed_version --> version $config_php_conges_version) ";
    log_action(0, "", "", $comment_log);

    return $currentPatch;
}

$installedVersion = \install\Fonctions::getInstalledVersion();
$versionLastMaj = miseAJour($versionLastMaj, $config_php_conges_version);

/* Reset du token d'instance à chaque version */
\includes\SQL::query('UPDATE `conges_appli` SET appli_valeur =  "' . hash('sha256', time() . rand()) . '" WHERE appli_variable = "token_instance"');

$sql_update_version = "UPDATE conges_config SET conf_valeur = '$config_php_conges_version' WHERE conf_nom = 'installed_version' ";
\includes\SQL::query($sql_update_version) ;

$sql_update_date = 'UPDATE `conges_appli` SET appli_valeur = "' . $versionLastMaj . '" WHERE appli_variable = "version_last_maj" LIMIT 1';
\includes\SQL::query($sql_update_date);


// quoi faire ensuite ?!
