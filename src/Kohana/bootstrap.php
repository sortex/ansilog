<?php
define('KOHANA_START_TIME', microtime(TRUE));
define('KOHANA_START_MEMORY', memory_get_usage());
define('EXT', '.php');

// Define i18n translate alias function
if ( ! function_exists('__'))
{
	function __($string, array $values = NULL, $lang = 'en-us')
	{
		return I18n::translate($string, $values, $lang);
	}
}

if (isset($_SERVER['SERVER_PROTOCOL']))
{
	HTTP::$protocol = $_SERVER['SERVER_PROTOCOL'];
}

if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = constant('Kohana::'.ENVNAME);
}

if (Kohana::$environment === Kohana::DEVELOPMENT)
{
	Kohana::$profiling = TRUE;
}

Kohana::$is_windows = (DIRECTORY_SEPARATOR === '\\');

$_GET    = Kohana::sanitize($_GET);
$_POST   = Kohana::sanitize($_POST);
$_COOKIE = Kohana::sanitize($_COOKIE);

ob_start();
set_exception_handler(array('Kohana_Exception', 'handler'));
set_error_handler(array('Kohana', 'error_handler'));
register_shutdown_function(array('Kohana', 'shutdown_handler'));

Kohana::modules([
	'app'     => APPPATH,
	'ansilog' => DOCROOT.'src/Ansilog',         // Ansilog platform
	'theme'   => DOCROOT.'src/Themes/_default',
	'kohana'  => DOCROOT.'src/Kohana',          // Kohana extensions
	'minion'  => MODPATH.'minion',              // Kohana extensions
	'core'    => MODPATH.'core',                // Kohana/core
]);
Kohana::init_modules();

spl_autoload_register(array('Kohana', 'auto_load'));
