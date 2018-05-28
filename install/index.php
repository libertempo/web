<?php
define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

//include_once ROOT_PATH .'fonctions_conges.php' ;
$_SESSION['lang'] = 'fr_FR';

include_once INCLUDE_PATH .'fonction.php';
session_delete();
include_once ROOT_PATH .'fonctions_conges.php' ;
if (!empty(session_id())) {
    session_regenerate_id(false);
}



// TODO 1.10 : la suppression des langues se fait en plusieurs temps.
// Les versions suivantes devront supprimer toute info liée aux langues.
$lang = 'fr_FR';
//recup de la config db

include CONFIG_PATH . 'dbconnect.php';
include_once ROOT_PATH . 'version.php';

$data = ['serveur' => $mysql_serveur, 'base' => $mysql_database, 'user' => $mysql_user, 'password' => $mysql_pass];
try {
    \install\Fonctions::setDataConfigurationApi($data);
} catch (\Exception $e) {
    echo 'Échec de l\'installation / mise à jour : ' . $e->getMessage();
    exit();
}
\install\Fonctions::installationMiseAJour($lang);
echo '<META HTTP-EQUIV=REFRESH CONTENT="0; URL=../">';
