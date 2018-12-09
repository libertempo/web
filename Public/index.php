<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$rewritten = [
    '/authentification',
    '/calendrier',
    '/config/general',
    '/config/type_absence',
    '/config/mail',
    '/config/logs',
    '/deconnexion',
    '/hr/page_principale',
    '/hr/ajout_user',
    '/hr/ajout_groupe',
    '/hr/liste_groupe',
    '/hr/traitement_demandes',
    '/hr/jours_chomes',
    '/hr/ajout_conges',
    '/hr/jours_fermeture',
    '/hr/cloture_year',
];

if (!in_array($uri, $rewritten, true)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
$explodedUri = explode('/', $uri);
$urn = $explodedUri[1] ?? null;
$resource = $explodedUri[2] ?? null;

switch ($urn) {
    case 'authentification':
        require_once ROOT_PATH . 'index.php';
        break;
    case 'calendrier':
        require_once ROOT_PATH . 'calendrier.php';
        break;
    case 'config':
        $_GET['onglet'] = $resource;
        require_once ROOT_PATH . 'config/index.php';
        break;
    case 'deconnexion':
        require_once ROOT_PATH . 'deconnexion.php';
        break;
    case 'hr':
        $_GET['onglet'] = $resource;
        require_once ROOT_PATH . 'hr/hr_index.php';
        break;

    default:
        header('HTTP/1.0 404 Not Found');
        break;
}
