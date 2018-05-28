<?php
define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';
include_once INCLUDE_PATH . 'fonction.php';
session_delete();
include_once ROOT_PATH .'fonctions_conges.php' ;
include_once CONFIG_PATH . 'dbconnect.php';
if (!empty(session_id())) {
    session_regenerate_id(false);
}

$data = ['serveur' => $mysql_serveur, 'base' => $mysql_database, 'user' => $mysql_user, 'password' => $mysql_pass];
try {
    \install\Fonctions::setDataConfigurationApi($data);
} catch (\Exception $e) {
    echo 'Échec de l\'installation / mise à jour : ' . $e->getMessage();
    exit();
}

\install\Fonctions::installationMiseAJour();
echo '<META HTTP-EQUIV=REFRESH CONTENT="0; URL=../">';
