<?php


defined( '_PHP_CONGES' ) or die( 'Restricted access' );
defined( 'ROOT_PATH' ) or die( 'ROOT_PATH not defined !' );

if (!defined( 'DEFINE_INCLUDE' )) {
	define('DEFINE_INCLUDE',	true);
	define('SHOW_SQL',			false);
	define('ERROR_MAIL_REPORT',	'your@mail.adress');// remove this if you don't want receive mails when a SQL error is found.
	
	define('LIBRARY_PATH',		ROOT_PATH . 'library/');
	define('INCLUDE_PATH',		ROOT_PATH . 'include/');
	define('CONFIG_PATH',		ROOT_PATH . 'cfg/');
	define('INSTALL_PATH',		ROOT_PATH . 'install/');
	define('LOCALE_PATH',		ROOT_PATH . 'locale/');
	define('DUMP_PATH',			ROOT_PATH . 'dump/');
	define('TEMPLATE_PATH',		ROOT_PATH . 'template/reboot/');
	
	define('PLUGINS_DIR',		ROOT_PATH . "include/plugins/");
	define('ICS_SALT',		'Jao%iT}'); //modify salt for more security with ics export

	/*--- twiguification ---*/
	require_once ROOT_PATH . 'vendor/autoload.php';

	$loader = new Twig_Loader_Filesystem(ROOT_PATH . 'template/twig');
	// global $twig;

	$twig = new Twig_Environment($loader, array(
	    'cache' => ROOT_PATH . 'template/twig/cache',
	));
}
