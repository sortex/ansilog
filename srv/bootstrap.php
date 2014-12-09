<?php
// Kohana bootstrap
// -------------------------------------------------------------
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
	'ko-core' => MODPATH.'core',                // Kohana/core
]);
Kohana::init_modules();

spl_autoload_register(array('Kohana', 'auto_load'));

// Prepare configuration stack
// -------------------------------------------------------------
Kohana::$config = (new Config)
	->attach(new Config_Ini(APPPATH))
	->attach(new Config_Ini(DOCROOT.'etc/environments'))
	->preload('all');

// Pre-load and merge app's specific configuration,
// and environment-specific configuration files.
if (Kohana::$environment !== Kohana::PRODUCTION)
{
 	Kohana::$config->preload(ENVNAME, TRUE);
}

Kohana::$config->preload('app', TRUE);

// Prepare logging stack
// -------------------------------------------------------------
Kohana::$log = Log::instance();

// Attach multiple configurable loggers
foreach (Kohana::$config->load('app')->log as $name => $level)
{
	if ($level  == 'off' || $level == 'false') continue;

	$opts = Kohana::$config->load('log')->{$name};
	$class = 'Log_'.ucfirst(strtolower($opts['type']));
	$level = $level == 'on' || $level == 'true' ? [] : (int) $level;
	Kohana::$log->attach(new $class($opts), $level);
}
unset($type, $class, $level, $opts);

// Init application
// -------------------------------------------------------------
$app_config = Kohana::$config->load('app');

// Configure app and provide Kohana classes' configuration
$app = new Ansilog\App($app_config);

Session::$default   = $app_config->session;
Cookie::$salt       = $app_config->salt;
Cache::$default     = $app_config->cache;
Auth::$session_type = Session::$default;

if (Kohana::$environment === Kohana::DEVELOPMENT)
{
	$app->setProfiler(new Tool_Profiler);
}

// Set current language from global collection
if ($langs = $app_config->get('languages') && ! empty($langs))
{
	// First is default
	I18n::lang($langs[0]);
}

// Set the base URL
Kohana::$base_url = rtrim($app_config['base_uri'], '/').'/';

// Init cache
// -------------------------------------------------------------
Kohana::$cache = Cache::instance();

// Don't cache find_file in development
if (Kohana::$environment !== Kohana::DEVELOPMENT)
{
	// Enables Kohana::find_file caching
	Kohana::$caching = TRUE;
	Kohana::$cache_key = $app_config->name;
	Kohana::load_cached_finds();
}

unset($app_config, $langs);
return $app;
