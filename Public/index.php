<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$rewritten = [
    '/authentification',
    '/utilisateur/liste_conge'
];

if (!in_array($uri, $rewritten, true)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
$utilisateur = '/utilisateur';
$authentification = '/authentification';

if ($utilisateur === substr($uri, 0, strlen($utilisateur))) {
    $_GET['onglet'] = 'liste_conge';
    require_once ROOT_PATH . 'utilisateur/user_index.php';
} elseif ($authentification === substr($uri, 0, strlen($authentification))) {
    require_once ROOT_PATH . 'index.php';
}
