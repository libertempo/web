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
    define('ABSOLUTE_SYSPATH', dirname(__FILE__) . DS);
    define('DEBUG_SYSPATH',    ABSOLUTE_SYSPATH . 'debug' . DS);
    define('PUBLIC_PATH',      ROOT_PATH . 'Public/');
    define('VIEW_PATH', ROOT_PATH . 'App' . DS . 'Views' . DS);
    define('ASSETS_PATH',      PUBLIC_PATH . 'Assets/');
    define('JS_PATH',          ASSETS_PATH . 'Js/');
    define('IMG_PATH',         ASSETS_PATH . 'Img/');
    define('FONT_PATH',        ASSETS_PATH . 'Font/');
    define('CSS_PATH',         ASSETS_PATH . 'Css/');
    define('LIBRARY_PATH',     ROOT_PATH . 'library/');
    define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
    define('CONFIG_PATH',      realpath(ABSOLUTE_SYSPATH) . DS . 'cfg'. DS);
    define('INSTALL_PATH',     ROOT_PATH . 'install/');
    define('LOCALE_PATH',      ROOT_PATH . 'locale/');
    define('DUMP_PATH',        ROOT_PATH . 'dump/');
    define('BACKUP_PATH',      ROOT_PATH . 'backup' . DS);
    define('TEMPLATE_PATH',    PUBLIC_PATH . 'template/');
    define('API_SYSPATH', ABSOLUTE_SYSPATH . 'vendor' . DS . 'Libertempo' . DS . 'libertempo-api' . DS);
    define('API_PATH', ROOT_PATH . 'api/');
    define('PLUGINS_DIR',      INCLUDE_PATH . 'plugins/');
    define('NIL_INT',          -1);
    define('STATUS_ACTIVE',    1);
    define('STATUS_DELETED',   2);
    define('SESSION_DURATION', 20*60);

    require_once ROOT_PATH . 'vendor/autoload.php';
    require_once ROOT_PATH . 'vendor/raveren/kint/Kint.class.php';
    require_once CONFIG_PATH . 'env.php';

    $config = new \App\Libraries\Configuration(\includes\SQL::singleton());

    \Kint::enabled(false);
    \Kint::$theme = 'solarized-dark';

    switch (ENV) {
        case ENV_PROD:
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
            ini_set("display_errors", 0);
            \Rollbar\Rollbar::init([
                'access_token' => 'ee012bd0fc724b79ac6b1f28a8f74e44',
                'environment' => 'production',
                'code_version' => $config->getInstalledVersion(),
            ]);
            break;
        case ENV_DEV:
            \Kint::enabled(true);
            ini_set("display_errors", 1);
            error_reporting(-1);
            // no break;
            \Rollbar\Rollbar::init([
                'access_token' => 'ee012bd0fc724b79ac6b1f28a8f74e44',
                'environment' => 'development',
                'code_version' => $config->getInstalledVersion(),
            ]);
            break;
        case ENV_TEST:
            \Kint::enabled(true);
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            ini_set("display_errors", 1);
            \Rollbar\Rollbar::init([
                'access_token' => 'ee012bd0fc724b79ac6b1f28a8f74e44',
                'environment' => 'testing',
                'code_version' => $config->getInstalledVersion(),
            ]);
            break;
    }
    session_start();

    /* Définition de headers de sécurité */
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}
