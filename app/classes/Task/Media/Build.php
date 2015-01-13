<?php defined('SYSPATH') or die('No direct script access.');
/**
 * The 'media:build' task creates a global site folder for media files
 * using the APP_VERSION as the folder name, it create symlinks
 * to all media files throughout Kohana's CFS scope
 *
 * Available options:
 *
 * --pattern=name
 *
 *  Specifies the named pattern to use
 *
 *
 * @package    Sortex
 * @author     Sortex Systems Development Ltd.
 * @copyright  (c) 2011-2014 Sortex
 * @license    BSD
 * @link       http://www.sortex.co.il
 */
class Task_Media_Build extends Minion_Task {

	protected $_options = array(
		'pattern' => NULL,
	);

	/**
	 * Execute the task
	 *
	 * @param array $options Configuration
	 */
	protected function _execute()
	{
		$count = $this->build($this->get_options());
		Minion_CLI::write('Media make complete, '.$count.' files processed');
	}

	/**
	 * Execute the task
	 *
	 * @param  array  $options  Configuration
	 * @return int
	 * @throws ErrorException
	 */
	protected function build(array $options)
	{
		if (empty($options['pattern']))
			throw new Kohana_Exception("Please provide a build pattern --pattern\nSee help for more info");

		$config  = Kohana::$config->load('media');
		$tmp_dir = Arr::path($config, 'build.tmp_dir');
		$patterns = Arr::path($config, 'build.patterns.'.$options['pattern']);

		if (empty($patterns))
			throw new Kohana_Exception('Could not load requested pattern for pattern ":pattern"',
				[ ':pattern' => $options['pattern'] ]);

		if (empty($tmp_dir))
			throw new Kohana_Exception('Missing tmp_dir configuration');

		$files = [];
		foreach ($patterns as $directory => $pattern)
		{
			// Clear symlinks
			$cmd = 'rm -rf '.escapeshellarg($tmp_dir.'/'.$directory);
//			exec($cmd, $output, $ret_val);

			//Minion_CLI::write("\n$directory => $pattern\n");
			$media = Arr::flatten(Kohana::list_files($directory));
			foreach ($media as $relative => $filepath)
			{
				// Check if the path matches the pattern for the compiler
				//Minion_CLI::write('('.$options['pattern'].') TRY '.$relative);
				if (preg_match($pattern, $relative))
				{
					//Minion_CLI::write('('.$options['pattern'].') MATCH '.$filepath);
					$files[$relative] = $filepath;
				}
				//else
				//{
					//Minion_CLI::write('('.$options['pattern'].') NO-MATCH '.$filepath);
				//}
			}
		}

		if ( ! empty($files))
		{
			foreach ($files as $relative_path => $absolute_path)
			{
				$destination = $tmp_dir.'/'.$relative_path;
				$this->copy_file($absolute_path, $destination, $config['build']['symlinks']);
			}
		}
		return count($files);
	}

	/**
	 * Create missing folders, and copy files or symlink them to destination
	 *
	 * @param      $source
	 * @param      $destination
	 * @param bool $symlink
	 */
	public function copy_file($source, $destination, $symlink = TRUE)
	{
		$directory = pathinfo($destination, PATHINFO_DIRNAME);
		if ( ! is_dir($directory))
		{
			mkdir($directory, 0770, TRUE);
		}

		if ($symlink)
		{
			if (Kohana::$is_windows)
			{
				exec('mklink '.escapeshellarg($destination).' '.escapeshellarg($source));
			}
			else
			{
				exec('ln -sf '.escapeshellarg($source).' '.escapeshellarg($destination));
			}
		}
		else
		{
			copy($source, $destination);
		}
	}

}
