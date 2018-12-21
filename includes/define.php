<?php
define('_PHP_CONGES', 1);
defined( 'ROOT_PATH' ) or die( 'ROOT_PATH not defined !' );

if (!defined( 'DEFINE_INCLUDE' )) {
    define('ENV_DEV', 1);
    define('ENV_TEST', 2);
    define('ENV_PROD', 3);
    define('DEFINE_INCLUDE',   true);
    define('SHOW_SQL',         false);
    define('DS',               DIRECTORY_SEPARATOR);
    define('ABSOLUTE_SYSPATH', dirname(__FILE__, 2) . DS);
    define('PUBLIC_PATH',      ROOT_PATH . 'Public/');
    define('VIEW_PATH', ROOT_PATH . 'App' . DS . 'Views' . DS);
    define('ASSETS_PATH',      PUBLIC_PATH . 'Assets/');
    define('JS_PATH',          ASSETS_PATH . 'Js/');
    define('IMG_PATH',         ASSETS_PATH . 'Img/');
    define('FONT_PATH',        ASSETS_PATH . 'Font/');
    define('CSS_PATH',         ASSETS_PATH . 'Css/');
    define('LIBRARY_PATH',     ROOT_PATH . 'library/');
    define('NODE_PATH', ROOT_PATH . 'node_modules' . DS);
    define('CONFIG_PATH',      realpath(ABSOLUTE_SYSPATH) . DS . 'cfg'. DS);
    define('LOCALE_PATH',      ROOT_PATH . 'locale/');
    define('TEMPLATE_PATH',    PUBLIC_PATH . 'template/');
    define('NIL_INT',          -1);
    define('STATUS_ACTIVE',    1);
    define('STATUS_DELETED',   2);
    define('SESSION_DURATION', 20*60);

    require_once ROOT_PATH . 'vendor/autoload.php';
    require_once ROOT_PATH . 'vendor/raveren/kint/Kint.class.php';
    require_once CONFIG_PATH . 'env.php';
    require_once INCLUDE_PATH . 'fonctions_conges.php' ;
    require_once INCLUDE_PATH . 'fonction.php';
    require_once INCLUDE_PATH . 'fonctions_calcul.php';

    switch (ENV) {
        case ENV_PROD:
        default:
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
            ini_set("display_errors", 0);
            $environnement = 'production';
            \Kint::enabled(false);
            break;
        case ENV_DEV:
            \Kint::enabled(true);
            error_reporting(-1);
            ini_set("display_errors", 1);
            $environnement = 'development';
            break;
        case ENV_TEST:
            \Kint::enabled(true);
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            ini_set("display_errors", 0);
            $environnement = 'test';
            break;
    }
    if (!empty(LOGGER_TOKEN)) {
        // @TODO : c'est dégueu, enlevez ça quand la nouvelle installation par CLI sera là
        try {
            $config = new \App\Libraries\Configuration(\includes\SQL::singleton());
            $version = $config->getInstalledVersion();
        } catch (\mysqli_sql_exception $e) {
            $version = null;
        }

        \Rollbar\Rollbar::init([
            'access_token' => LOGGER_TOKEN,
            'environment' => $environnement,
            'code_version' => $version,
            'use_error_reporting' => true,
            'allow_exec' => false,
            'included_errno' => E_ALL,
        ]);
        \Rollbar\Rollbar::addCustom('access_key', LOGGER_TOKEN);
    }

    session_start();

    /* Définition de headers de sécurité */
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}
