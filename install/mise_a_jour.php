<?php

define('ROOT_PATH', '../');
require_once ROOT_PATH . 'define.php';

include_once ROOT_PATH .'fonctions_conges.php' ;
include_once INCLUDE_PATH .'fonction.php';
include ROOT_PATH .'version.php' ;

$PHP_SELF = filter_input(INPUT_SERVER, 'PHP_SELF', FILTER_SANITIZE_URL);

//recup de la langue
$lang=(isset($_GET['lang']) ? $_GET['lang'] : ((isset($_POST['lang'])) ? $_POST['lang'] : "") ) ;
$lang = htmlentities($lang, ENT_QUOTES | ENT_HTML401);
if (!in_array($lang, ['fr_FR', 'en_US', 'es_ES'], true)) {
    $lang = '';
}

// recup des parametres
$action = (isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : "")) ;
$action = htmlentities($action, ENT_QUOTES | ENT_HTML401);

$version = (isset($_GET['version']) ? $_GET['version'] : (isset($_POST['version']) ? $_POST['version'] : "")) ;
$version = htmlentities($version, ENT_QUOTES | ENT_HTML401);
$etape = (isset($_GET['etape']) ? $_GET['etape'] : (isset($_POST['etape']) ? $_POST['etape'] : 0 )) ;
$etape = htmlentities($etape, ENT_QUOTES | ENT_HTML401);


if($version == 0) {  // la version à mettre à jour dans le formulaire de index.php n'a pas été choisie : renvoit sur le formulaire
    redirect( ROOT_PATH . 'install/index.php?lang='.$lang);
}

header_popup(' PHP_CONGES : '. _('install_maj_titre_1') );

// affichage du titre
echo "<center>\n";
echo "<br><H1><img src=\"".IMG_PATH."tux_config_32x32.png\" width=\"32\" height=\"32\" border=\"0\" title=\"". _('install_install_phpconges') ."\" alt=\"". _('install_install_phpconges') ."\"> ". _('install_maj_titre_2') ."</H1>\n";
echo "<br><br>\n";

// $config_php_conges_version est fourni par include_once ROOT_PATH .'version.php' ;
\install\Fonctions::lance_maj($lang, $version, $config_php_conges_version, $etape);

bottom();
