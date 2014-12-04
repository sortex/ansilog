<?php
if (PHP_SAPI !== 'cli')
	throw new Exception('Tasks should be run from command-line.');

// Set the document root
define('DOCROOT', realpath(dirname(__FILE__).'/../..').DIRECTORY_SEPARATOR);

require realpath(__DIR__.'/..').'/environment.php';
require DOCROOT.'vendor/autoload.php';
require DOCROOT.'src/Kohana/bootstrap.php';

// Prepare configuration stack
// -------------------------------------------------------------
Kohana::$config = (new Config)
	->attach(new Config_Ini(APPPATH))
	->attach(new Config_Ini(DOCROOT.'etc/environments'))
	// Pre-load and merge app's specific configuration,
	// and environment-specific configuration files.
	->preload('all')
	->preload('testing', TRUE)
	->preload('app', TRUE);

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

// Set current language from global collection
if ($langs = $app_config->get('languages') && ! empty($langs))
{
	// First is default
	I18n::lang($langs[0]);
}

Minion_Task::factory(Minion_CLI::options(), [ 'app' => $app ])->execute();
