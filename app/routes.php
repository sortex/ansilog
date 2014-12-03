<?php

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
