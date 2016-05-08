<?php
define('_PHP_CONGES', 1);
defined( 'ROOT_PATH' ) or die( 'ROOT_PATH not defined !' );

if (!defined( 'DEFINE_INCLUDE' )) {
    define('ENV_DEV', 1);
    define('ENV_TEST', 2);
    define('ENV_PROD', 3);
    define('ENV', ENV_PROD);
    define('DEFINE_INCLUDE',   true);
    define('SHOW_SQL',         false);
    define('ABSOLUTE_SYSPATH', dirname(__FILE__) . '/');
    define('DEBUG_SYSPATH',    ABSOLUTE_SYSPATH . 'debug/');
    define('PUBLIC_PATH',      ROOT_PATH . 'Public/');
    define('ASSETS_PATH',      PUBLIC_PATH . 'Assets/');
    define('JS_PATH',          ASSETS_PATH . 'Js/');
    define('IMG_PATH',         ASSETS_PATH . 'Img/');
    define('FONT_PATH',        ASSETS_PATH . 'Font/');
    define('CSS_PATH',         ASSETS_PATH . 'Css/');
    define('LIBRARY_PATH',     ROOT_PATH . 'library/');
    define('INCLUDE_PATH',     ROOT_PATH . 'includes/');
    define('CONFIG_PATH',      ROOT_PATH . 'cfg/');
    define('INSTALL_PATH',     ROOT_PATH . 'install/');
    define('LOCALE_PATH',      ROOT_PATH . 'locale/');
    define('DUMP_PATH',        ROOT_PATH . 'dump/');
    define('TEMPLATE_PATH',    ROOT_PATH . 'template/reboot/');
    define('PLUGINS_DIR',      INCLUDE_PATH . 'plugins/');

    require_once ROOT_PATH . 'vendor/autoload.php';
    require_once ROOT_PATH . 'vendor/raveren/kint/Kint.class.php';
    \Kint::enabled(false);
    \Kint::$theme = 'solarized-dark';
    switch (ENV) {
        case ENV_DEV:
            error_reporting(-1);
            \Kint::enabled(true);
            // no break;
        case ENV_TEST:
            \Kint::enabled(true);
            if (ENV === ENV_TEST) {
                error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
            }
            ini_set("display_errors", 1);

            /**
             * Logue les variables à déboguer
             */
            function debug()
            {
                global $debug;
                $debug[] = [
                    'Kint' => @d(func_get_args()),
                    'Backtrace' => '<pre>' . print_r(debug_backtrace(), true) . '</pre>',
                    'LastError' => error_get_last(),
                ];
            }
            register_shutdown_function(function () {
                global $debug;
                if (empty($debug)) {
                    return;
                }
                if (!is_dir(DEBUG_SYSPATH)) {
                    mkdir(DEBUG_SYSPATH);
                }
                $file = fopen(DEBUG_SYSPATH . 'dbg-' . date('Y-m-d') . '.html', 'wb');
                if (!is_resource($file)) {
                    return;
                }
                foreach ($debug as $v) {
                    foreach ($v as $title => $data) {
                        fwrite($file, '####################<br># ' . $title. '<br>####################<br>');
                        fwrite($file, $data . '<br><br>');
                    }
                }

            });
            break;
    }
}
