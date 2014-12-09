<?php
require realpath(dirname(__FILE__).'/..').'/environment.php';
require DOCROOT.'vendor/autoload.php';

$app = require DOCROOT.'srv/bootstrap.php';

// Routes
// -------------------------------------------------------------
// Use cache in non-dev environments
if (Kohana::$environment === Kohana::DEVELOPMENT || ! Route::cache())
{

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

