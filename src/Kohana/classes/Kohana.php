<?php
/**
 * Overriding Kohana's core
 */
class Kohana extends Kohana_Core {

	// Overriding Kohana_Core:
	protected static $_init = TRUE;
	public static $index_file = FALSE;
	public static $profiling  = FALSE;

	// New statics:
	public static $cache;
	public static $cache_key = '';

	/**
	 * Raise the init flag
	 */
	public static function start()
	{
		Kohana::$_init = TRUE;
	}

	/**
	 * [!] Overwriting Kohana's simple cache
	 *
	 * @throws  Kohana_Exception
	 * @param   string  $name       name of the cache
	 * @param   mixed   $data       data to cache
	 * @param   integer $lifetime   number of seconds the cache is valid for
	 * @return  mixed    for getting
	 * @return  boolean  for setting
	 */
	public static function cache($name, $data = NULL, $lifetime = NULL)
	{
		if ($lifetime === NULL)
		{
			// Use the default lifetime
			$lifetime = Kohana::$cache_life;
		}

		if ($data === NULL)
		{
			return Kohana::$cache->get($name);
		}
		else
		{
			Kohana::$cache->set($name, $data, $lifetime);
			return TRUE;
		}
	}

	public static function load_cached_finds()
	{
		Kohana::$_files = Kohana::cache('Kohana::find_file('.Kohana::$cache_key.')');
	}

	/**
	 * [!] Overwriting Kohana's shutdown_handler
	 * Catches errors that are not caught by the error handler, such as E_PARSE.
	 *
	 * @uses    Kohana_Exception::handler
	 * @return  void
	 */
	public static function shutdown_handler()
	{
		if ( ! Kohana::$_init)
		{
			// Do not execute when not active
			return;
		}

		try
		{
			if (Kohana::$caching === TRUE AND Kohana::$_files_changed === TRUE)
			{
				// Write the file path cache
				Kohana::cache('Kohana::find_file('.Kohana::$cache_key.')', Kohana::$_files);
			}
		}
		catch (Exception $e)
		{
			// Pass the exception to the handler
			Kohana_Exception::handler($e);
		}

		if (Kohana::$errors AND $error = error_get_last() AND in_array($error['type'], Kohana::$shutdown_errors))
		{
			// Clean the output buffer
			ob_get_level() AND ob_clean();

			// Fake an exception for nice debugging
			Kohana_Exception::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));

			// Shutdown now to avoid a "death loop"
			exit(1);
		}
	}

}
