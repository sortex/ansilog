<?php
class Media {

	public static function url($filepath)
	{
		return Route::url('media', [
			'filepath' => $filepath,
			'uid'      => Kohana::$config->load('app')->revision,
			'name'     => Kohana::$config->load('app')->name,
		]);
	}

	public static function uri($filepath)
	{
		return Route::get('media')->uri([
			'filepath' => $filepath,
			'uid'      => Kohana::$config->load('app')->revision,
			'name'     => Kohana::$config->load('app')->name,
		]);
	}

}
