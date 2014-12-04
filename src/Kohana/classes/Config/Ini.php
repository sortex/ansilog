<?php
class Config_Ini implements Kohana_Config_Reader {

	/**
	 * The directory where config files are located
	 * @var string
	 */
	protected $_directory = '';

	/**
	 * Creates a new file reader using the given directory as a config source
	 *
	 * @param string    $directory  Absolute configuration directory to search
	 */
	public function __construct($directory)
	{
		// Set the configuration directory name
		$this->_directory = rtrim($directory, '/');
	}

	/**
	 * Load and merge all of the configuration files in this group.
	 *
	 *     $config->load($name);
	 *
	 * @param   string  $group  configuration group name
	 * @return  $this   current object
	 * @uses    Kohana::load
	 */
	public function load($group)
	{
		if (Kohana::$profiling === TRUE)
		{
			$benchmark_dirs = Profiler::start(
				'Directory Lookups',
				Debug::path($this->_directory)
			);
		}
		$path   = $this->_directory.DIRECTORY_SEPARATOR.$group.'.ini';
		$groups = [];
		if (is_file($path))
		{
			if (Kohana::$profiling === TRUE)
			{
				$benchmark_config = Profiler::start(
					'Config loaded',
					Debug::path($this->_directory.'/'.$group)
				);
			}

			$config = parse_ini_file($path, TRUE);

			// Look for nested groups and re-group them
			foreach ($config as $group => $values)
			{
				if (strpos($group, ':') !== FALSE)
				{
					$keys = explode(':', $group);
					// Find the inner-most array path
					$array = & $groups;
					while (count($keys) > 1)
					{
						$key = array_shift($keys);
						ctype_digit($key) AND $key = (int) $key;
						isset($array[$key]) OR $array[$key] = [];
						$array = & $array[$key];
					}

					// We've reached the last path part
					$last = array_shift($keys);
					// Do not overwrite anything already there
					isset($array[$last])
						? $array[$last] += $values
						: $array[$last] = $values;

					unset($array);
				}
				else
				{
					// Do not overwrite anything already there
					empty($groups[$group])
						? $groups[$group] = $values
						: $groups[$group] += $values;
				}
			}
		}

		isset($benchmark_dirs)
			AND Profiler::stop($benchmark_dirs);
		isset($benchmark_config)
			AND Profiler::stop($benchmark_config);

		return $groups;
	}

}
