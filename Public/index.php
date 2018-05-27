<?php
define('ROOT_PATH', '../');
define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
require_once INCLUDE_PATH . 'define.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$rewritten = [
    '/authentification',
    '/config/general',
    '/config/type_absence',
];

if (!in_array($uri, $rewritten, true)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}
list(,$urn, $resource) = explode('/', $uri);

switch ($urn) {
    case 'config':
        $_GET['onglet'] = $resource;
        require_once ROOT_PATH . 'config/index.php';
        break;

    default:
        header('HTTP/1.0 404 Not Found');
        break;
}
