<?php


define('_PHP_CONGES', 1);
defined( 'ROOT_PATH' ) or die( 'ROOT_PATH not defined !' );

if (!defined( 'DEFINE_INCLUDE' )) {
    define('MODE_DEV', 1);
    define('MODE_TEST', 2);
    define('MODE_PROD', 3);
    define('MODE', MODE_PROD);
    define('DEBUG_MODE',     true);
    define('DEFINE_INCLUDE', true);
    define('SHOW_SQL',       false);
    define('ABSOLUTE_PATH',  dirname(__FILE__) . '/');
    define('DEBUG_PATH',     ABSOLUTE_PATH . 'debug/');
    define('LIBRARY_PATH',   ROOT_PATH . 'library/');
    define('INCLUDE_PATH',   ROOT_PATH . 'includes/');
    define('CONFIG_PATH',    ROOT_PATH . 'cfg/');
    define('INSTALL_PATH',   ROOT_PATH . 'install/');
    define('LOCALE_PATH',    ROOT_PATH . 'locale/');
    define('DUMP_PATH',      ROOT_PATH . 'dump/');
    define('TEMPLATE_PATH',  ROOT_PATH . 'template/reboot/');
    define('PLUGINS_DIR',    INCLUDE_PATH . 'plugins/');

    require_once ROOT_PATH . 'vendor/autoload.php';
    switch (MODE) {
        case MODE_DEV:
            error_reporting(-1);
            ini_set("display_errors", 1);
            // no break;
        case MODE_TEST:
            function debug()
            {
                global $debug;
                $debug[] = [
                    'params'    => func_get_args(),
                    'backtrace' => debug_backtrace(),
                    'lastError' => error_get_last(),
                ];
            }
            register_shutdown_function(function () {
                global $debug;
                if (empty($debug)) {
                    return;
                }
                $file = fopen(DEBUG_PATH . 'dbg-' . date('Y-m-d_H.i'), 'ab+');
                if (!is_resource($file)) {
                    return;
                }
                foreach ($debug as $v) {
                    fwrite($file, $v[0] . "\n" . var_export($v, true) . "\n");
                }
            });
            break;
    }
}
