<?php


define('_PHP_CONGES', 1);
defined( 'ROOT_PATH' ) or die( 'ROOT_PATH not defined !' );

if (!defined( 'DEFINE_INCLUDE' )) {
    define('DEBUG_MODE',        true);
	define('DEFINE_INCLUDE',	true);
	define('SHOW_SQL',			false);

	define('LIBRARY_PATH',		ROOT_PATH . 'library/');
	define('INCLUDE_PATH',		ROOT_PATH . 'includes/');
	define('CONFIG_PATH',		ROOT_PATH . 'cfg/');
	define('INSTALL_PATH',		ROOT_PATH . 'install/');
	define('LOCALE_PATH',		ROOT_PATH . 'locale/');
	define('DUMP_PATH',			ROOT_PATH . 'dump/');
	define('TEMPLATE_PATH',		ROOT_PATH . 'template/reboot/');

	define('PLUGINS_DIR',		INCLUDE_PATH . "plugins/");
	define('ICS_SALT',		'Jao%iT}'); //modify salt for more security with ics export

	/*--- twiguification ---*/
	require_once ROOT_PATH . 'vendor/autoload.php';
    if (DEBUG_MODE) {
        error_reporting(-1);
        ini_set("display_errors", 1);
    }

	$loader = new \Twig_Loader_Filesystem(ROOT_PATH . 'template/twig');
	// global $twig;

	$twig = new \Twig_Environment($loader, array(
	    'cache' => ROOT_PATH . 'template/twig/cache',
	));
}
