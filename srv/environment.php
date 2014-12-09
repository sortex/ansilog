<?php
/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link http://www.php.net/manual/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and strict warnings. Enable them by using: E_ALL | E_STRICT
 *
 * In a production environment, it is safe to ignore notices and strict warnings.
 * Disable them by using: E_ALL ^ E_NOTICE
 */
error_reporting(E_ALL | E_STRICT);

// Set the document root
define('DOCROOT', realpath(dirname(__FILE__).'/..').DIRECTORY_SEPARATOR);

// Define the environment
define('ENVNAME', strtolower(getenv('ENV_NAME') ?: 'development'));

/**
 * Set paths
 */
$vendor_path = 'vendor/';

$paths = array(
	'APPPATH' => 'app',
	'MODPATH' => $vendor_path.'kohana',
	'SYSPATH' => $vendor_path.'kohana/core',
);

foreach ($paths as $key => $path)
{
	// Make the path relative to the docroot, for symlink'd index.php
	if ( ! is_dir($path) AND is_dir(DOCROOT.$path))
	{
		$path = DOCROOT.$path;
	}

	// Define the absolute path
	define($key, realpath($path).DIRECTORY_SEPARATOR);
}
unset($paths);

// -- Environment setup ----------------------------------------

// Set the default locale.
// @link  http://php.net/setlocale
setlocale(LC_ALL, 'en_US.utf-8');

// Set the default time zone.
// @link  http://php.net/timezones
date_default_timezone_set('UTC');

// Enable the spl auto-loader for unserialization.
// @link  http://php.net/spl_autoload_call
// @link  http://php.net/manual/var.configuration.php#unserialize-callback-func
ini_set('unserialize_callback_func', 'spl_autoload_call');

// Set the MB extension encoding to the same character set
// @link  http://www.php.net/manual/function.mb-substitute-character.php
mb_internal_encoding('none');

// Enable xdebug parameter collection in development mode to improve fatal stack traces.
if (ENVNAME === 'development' && extension_loaded('xdebug'))
{
	 ini_set('xdebug.collect_params', 3);
}
