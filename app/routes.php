<?php

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
	]);

// Pages ----------------------

// Default site pages + language
Route::set('frontend', '<language>(/<slug>)',
		[ 'language' => '\w{2}(-\w{2})?', 'slug' => '.+' ]
	)
	->defaults([
		'directory'  => 'Site',
		'controller' => 'Page',
		'slug'       => '/',
	]);

// Default site pages, no language
Route::set('frontend-pages', '(<slug>)')
	->defaults([
		'directory'  => 'Site',
		'controller' => 'Page',
		'slug'       => '/',
	]);

// ----------------------------
