<?php

class Log_File extends Log_Writer {

	protected $path = '';
	protected $directory = FALSE;
	protected $filename = '<name>.php';
	protected $as_dates = FALSE;

	protected $format = 'time --- level: body in file:line (ip)';

	/**
	 * Examples:
	 * - Classic Kohana YYYY/MM/DD.php
	 *   [ 'directory' => 'Y/m', 'filename' => 'd.php', 'as_dates' => TRUE ]
	 * - YYYY-MM.log
	 *   [ 'directory' => FALSE, 'filename' => 'Y-m.log', 'as_dates' => TRUE ]
	 * - <version>.log.php
	 *   [ 'filename' => '<version>.log.php', 'as_dates' => FALSE ]
	 */
	public function __construct(array $options)
	{
		if (empty($options['path']))
			throw new Exception('[Log/File] Missing path');

		$config = Kohana::$config->load('app');
		$params = [];
		foreach ([ 'name', 'version', 'revision', 'theme' ] as $item)
		{
			$params["<$item>"] = isset($config->{$item}) ? $config->{$item} : '';
		}

		$this->filename = strtr(
			Arr::get($options, 'filename', $this->filename), $params);

		$this->directory = strtr(
			Arr::get($options, 'directory', $this->directory), $params);

		$path = strtr($options['path'], $params);

		if ( ! is_dir($path) OR ! is_writable($path))
		{
			@mkdir($path, 02777);
			if ( ! is_dir($path))
				throw new Kohana_Exception('Directory :dir must be writable',
					[ ':dir' => Debug::path($path)]);

			// Set permissions (must be manually set to fix umask issues)
			chmod($path, 02777);
		}

		// Normalize the directory path
		$this->path = realpath($path).DIRECTORY_SEPARATOR;

		// Allow format override
		if (isset($options['format']))
		{
			$this->format = $options['format'];
		}
	}

	public function write(array $messages)
	{
		$path = $this->path;
		$groups = explode('/', $this->directory);
		foreach ($groups as $identifier)
		{
			(substr($path, -1) == '/') OR $path .= DIRECTORY_SEPARATOR;
			$path .= $this->as_dates ? date($identifier) : $identifier;

			if ( ! is_dir($path))
			{
				mkdir($path, 02777);

				// Set permissions (must be manually set to fix umask issues)
				chmod($path, 02777);
			}
		}

		// Set the name of the log file
		$filename = $path.DIRECTORY_SEPARATOR;
		if ($this->as_dates)
		{
			// Parse base filename as a date pattern
			$parts = explode('.', $this->filename);
			$filename .= date($parts[0]).'.'.$parts[1];
		}
		else
		{
			$filename .= $this->filename;
		}

		if ( ! file_exists($filename))
		{
			// Create the log file
			file_put_contents($filename, '<?php exit; ?>'.PHP_EOL.PHP_EOL);

			// Allow anyone to write to log files
			chmod($filename, 0666);
		}

		foreach ($messages as $message)
		{
			$message['ip'] = Request::$client_ip;

			// Write each message into the log file
			file_put_contents($filename, $this->format_message($message).PHP_EOL, FILE_APPEND);
		}
	}

	/**
	 * Overriding to support configurable format
	 */
	public function format_message(array $message, $format = '')
	{
		return parent::format_message($message, $this->format);
	}

}
