<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/**
 * Pour la migration vers VueJS :
 * puisque Vue est versatile et permet l'utilisation
 * comme librairie ou comme framework, il permet une migration douce.
 * Mais, le temps qu'on en fasse un framework,
 * nous devons stocker la valeur de session (token API) dans le code PHP,
 * token que nous transmettrons au script VueJS dans chaque page, puisqu'elles seront isolées.
 */

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
    '/hr/modif_groupe',
    '/hr/liste_groupe',
    '/hr/traitement_demandes',
    '/hr/jours_chomes',
    '/hr/ajout_conges',
    '/hr/jours_fermeture',
    '/hr/cloture_exercice',
    '/hr/liste_planning',
    '/hr/ajout_planning',
    '/hr/modif_planning',
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
        if ('cloture_exercice' === $resource) {
            $_GET['onglet'] = 'cloture_year';
        } else {
            $_GET['onglet'] = $resource;
        }
        require_once ROOT_PATH . 'hr/hr_index.php';
        break;

    default:
        header('HTTP/1.0 404 Not Found');
        break;
}
