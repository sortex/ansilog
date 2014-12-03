<?php
// Set the document root
define('DOCROOT', realpath(dirname(__FILE__).'/../..').DIRECTORY_SEPARATOR);

require DOCROOT.'srv/environment.php';
require DOCROOT.'vendor/autoload.php';
require DOCROOT.'src/Kohana/bootstrap.php';

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

// Routes
// -------------------------------------------------------------
// Use cache in non-dev environments
if (Kohana::$environment === Kohana::DEVELOPMENT || ! Route::cache())
{
	// MEDIA ----------------------

	Route::set('media', 'media/<uid>/<filepath>',
		[
			// Pattern to match the file path
			'filepath' => '.*',
			// Match the unique string that is not part of the media file
			'uid' => '.*?',
		])
		->defaults([
			'controller' => 'Media',
			'action'     => 'serve',
		]);

	// API ------------------------

	Route::set(
		'api',
		'api/<version>/<controller>(/<id>)(/<custom>)(.<format>)',
		[
			// version: number, optionally prepend "v", or latest. e.g.: v1/1/latest
			'version' => 'v?\d+|latest',
			// id: number or 'all'. e.g.: domain/api/1/user.json/all/group
			// id: number or 'all'. e.g.: domain/api/1/user.json/213/group
			'id'      => 'all|\d+',
			'format'  => 'json|xml|csv',
		])
		->defaults([
			'namespace'  => 'Ansilog',
			'directory'  => 'API',
			'id'         => FALSE,
			'custom'     => FALSE
		]);

	// Admin / Backoffice ---------

	// Admin Authentication
	Route::set('admin-auth', 'admin/<action>(/<custom>)',
		[
			'action' => '(login|logout|forgot|reset|register)'
		])
		->defaults([
			'namespace'  => 'Ansilog',
			'directory'  => 'Admin',
			'controller' => 'Auth',
		]);

	// Admin downloads
	Route::set('admin-downloads', 'admin/download/<action>')
		->defaults([
			'namespace'  => 'Ansilog',
			'directory'  => 'Admin',
			'controller' => 'download',
		]);

	// Admin default (Catch-all), all pages lead here
	// and let the client-side router continue from there.
	Route::set('admin-default', 'admin(/<custom>)',
			[ 'custom' => '.*' ]
		)
		->defaults([
			'namespace'  => 'Ansilog',
			'directory'  => 'Admin',
			'controller' => 'Start',
		]);

	// Services -------------------

	// Specify a sub-controller, e.g.: hook
	Route::set('hook', '<directory>/<controller>(/<action>)',
			[ 'controller' => 'hook' ]
		)
		->defaults([
			'namespace' => 'Ansilog'
		]);

	// Load app-specific routes
	require APPPATH.'routes.php';

	// Cache routes in non-dev envs and not in cli
	if (Kohana::$environment !== Kohana::DEVELOPMENT && PHP_SAPI !== 'cli')
	{
		Route::cache(TRUE);
	}
}

// RUN REQUEST -------------------------------------------------

echo Request::factory(TRUE, [ 'app' => $app ], FALSE)
		->execute()
		->send_headers(TRUE)
		->body();

// fin
