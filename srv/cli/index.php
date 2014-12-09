<?php
if (PHP_SAPI !== 'cli')
	throw new Exception('Tasks should be run from command-line.');

require realpath(__DIR__.'/..').'/environment.php';
require DOCROOT.'vendor/autoload.php';
require DOCROOT.'srv/bootstrap.php';

Minion_Task::factory(Minion_CLI::options(), [ 'app' => $app ])->execute();
