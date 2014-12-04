<?php
/**
 * Overriding Kohana's Exception base class
 */
class Kohana_Exception extends Kohana_Kohana_Exception {

	/**
	 * @var  string  Default error rendering view pattern
	 */
	public static $error_view = 'error/<format>/default';

	/**
	 * @var array $accept_types Supported mime-types
	 */
	protected static $accept_types = [
		'text/html',
		'application/json',
		'application/xml',
		'text/plain'
	];

	/**
	 * Override Exception handler to better handle exceptions at CLI
	 *
	 * @uses    Kohana_Exception::text
	 * @param   Exception   $e
	 * @return  boolean
	 */
	public static function handler(Exception $e)
	{
		if (PHP_SAPI == 'cli')
		{
			$response = Kohana_Exception::_handler($e);

			echo $response->body().PHP_EOL;

			// Never exit "0" after an exception.
			exit($e->getCode() ?: 1);
		}

		return parent::handler($e);
	}

	/**
	 * Get a Response object representing the exception
	 *
	 * @param   Exception  $e
	 * @return  Response
	 */
	public static function response(Exception $e)
	{
		try
		{
			$uid     = uniqid();
			$class   = get_class($e);
			$code    = $e->getCode();
			$message = $e->getMessage();
			$file    = $e->getFile();
			$line    = $e->getLine();
			$trace   = static::trace($e);
			$errors  = [];
			$format  = 'html';

			if (PHP_SAPI == 'cli')
			{
				self::$error_view_content_type = 'text/plain';
			}
			else
			{
				// Pick the right view by first finding the client's accept format.
				// Find the first accept header that matches one
				// of the supported defined list.
				$headers = HTTP::request_headers();
				foreach (self::$accept_types as $type)
				{
					if ($headers->accepts_at_quality($type))
					{
						self::$error_view_content_type = $type;
						break;
					}
				}
			}

			// If development environment AND client accepts html,
			// Use Kohana's view. Otherwise pick the one that matches
			// the client's browser preference.
			if (
				Kohana::$environment === Kohana::DEVELOPMENT
				&& self::$error_view_content_type == 'text/html'
			)
			{
				$error_view = parent::$error_view;
			}
			else
			{
				// Use the second part of the accept mime-type and exception
				// code to find the right view to load.
				$error_view = strtr(
					self::$error_view,
					[
						'<format>' => explode('/', self::$error_view_content_type)[1],
						'<code>' => $code
					]
				);
			}

			// Instantiate the error view
			$view = View::factory($error_view, get_defined_vars());

			// Prepare the response object
			$response = Response::factory();
			$response->status(($e instanceof HTTP_Exception) ? $e->getCode() : 500);
			$response->headers('Content-Type',
			 	Kohana_Exception::$error_view_content_type.'; charset='.Kohana::$charset);

			$response->body($view->render());
		}
		catch (Exception $e)
		{
			/**
			 * Things are going badly for us, Lets try to keep things under control by
			 * generating a simpler response object.
			 */
			$response = Response::factory();
			$response->status(500);
			$response->headers('Content-Type', 'text/plain');
			$text = '';
			do $text .= Kohana_Exception::text($e).$e->getTraceAsString();
				while ($e = $e->getPrevious());
			$response->body($text);
		}

		return $response;
	}

	/**
	 * Extract stacktrace nicely
	 *
	 * @param  Exception  $trace  Exception object
	 */
	public static function trace(Exception $e)
	{
		$code  = $e->getCode();
		$trace = $e->getTrace();

		/**
			* HTTP_Exceptions are constructed in the HTTP_Exception::factory()
			* method. We need to remove that entry from the trace and overwrite
			* the variables from above.
			*/
		if ($e instanceof HTTP_Exception AND $trace[0]['function'] == 'factory')
		{
			extract(array_shift($trace));
		}

		if ($e instanceof ErrorException)
		{
			/**
				* If XDebug is installed, and this is a fatal error,
				* use XDebug to generate the stack trace
				*/
			if (function_exists('xdebug_get_function_stack') AND $code == E_ERROR)
			{
				$trace = array_slice(array_reverse(xdebug_get_function_stack()), 4);

				foreach ($trace as & $frame)
				{
					/**
						* XDebug pre 2.1.1 doesn't currently set the call type key
						* http://bugs.xdebug.org/view.php?id=695
						*/
					if ( ! isset($frame['type']))
					{
						$frame['type'] = '??';
					}

					// Xdebug returns the words 'dynamic' and 'static' instead of using '->' and '::' symbols
					if ('dynamic' === $frame['type'])
					{
						$frame['type'] = '->';
					}
					elseif ('static' === $frame['type'])
					{
						$frame['type'] = '::';
					}

					// XDebug also has a different name for the parameters array
					if (isset($frame['params']) AND ! isset($frame['args']))
					{
						$frame['args'] = $frame['params'];
					}
				}
			}
		}

		/**
			* The stack trace becomes unmanageable inside PHPUnit.
			*
			* The error view ends up several GB in size, taking
			* serveral minutes to render.
			*/
		if (defined('PHPUnit_MAIN_METHOD'))
		{
			$trace = array_slice($trace, 0, 2);
		}

		return $trace;
	}

	/**
	 * Simple string stacktrace representation, without args
	 *
	 * @param  Exception  $trace  Exception object
	 */
	public static function print_trace($trace)
	{
		$str = '';
		$index = 0;
		foreach ($trace as $k => $v)
	 	{
			++$index;

			$class = isset($v['class']) ? $v['class'].'->' : '';
			$func = isset($v['function']) ? $v['function'] : '';
			if (isset($v['file']))
			{
				$file = Debug::path($v['file']).':'.$v['line'];
			}
			else
			{
				$file = 'PHP internal call';
			}

			$str .= "#$index: $class$func() [$file]\n";
		}
		return $str;
	}

}
