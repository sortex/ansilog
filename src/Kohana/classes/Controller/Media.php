<?php
/**
 * Media controller
 */
class Controller_Media extends Kohana_Controller {

	public $config;

	public function before()
	{
		parent::before();

		$this->config = Kohana::$config->load('media');
	}

	public function action_serve()
	{
		$filepath = $this->request->param('filepath');
		$uid = Kohana::$config->load('app')->revision;

		$cfs_file = Kohana::find_file('media', $filepath, FALSE);

		if ( ! $cfs_file)
			throw HTTP_Exception::factory(404);

		// http://www.webscalingblog.com/performance/caching-http-headers-last-modified-and-etag.html
		$size = (string) filesize($cfs_file);
		$time = (string) filemtime($cfs_file);

		// ETag is used instead of Last-Modified because it removes the need
		// for sending a `Cache-Control: must-revalidate` header. It is also
		// slightly more reliable it adds the filesize as second variable.
		//
		// To reduce the size of the ETag, the hex value of a crc32 is used.
		$etag = hash('crc32b', $size.'/'.$time);

		if ($this->request->headers('If-None-Match') === $etag)
		{
			// When the ETag matches, we abort the request by responding with
			// 304 (Not Modified) status, which tells the browser to use the
			// cached response.
			$this->response->status(304);
			return;
		}

		// Load the file content for caching and output
		$contents = file_get_contents($cfs_file);

		if ($this->config->cache)
		{
			// Save the contents to the public directory for future requests
			$public = strtr($this->config->public_dir, [
				'<uid>/'     => $uid ? $uid.'/' : '',
				'<name>'     => Kohana::$config->load('app')->name,
				'<filepath>' => $filepath,
			]);

			// Support relative or absolute path
			$public[0] === '/' OR $public = DOCROOT.$public;

			$directory = dirname($public);

			if ( ! is_dir($directory))
			{
				// Recursively create the directories needed for the file
				mkdir($directory.'/', 0777, TRUE);

				if ( ! is_dir($directory))
					throw HTTP_Exception::factory(500);

				// Set permissions (must be manually set to fix umask issues)
				chmod($directory, 02775);
			}

			file_put_contents($public, $contents);

			// Setting write permissions for group
			chmod($public, 0664);
		}

		$mime = File::mime_by_ext(pathinfo($cfs_file, PATHINFO_EXTENSION));

		// Set the proper headers to allow caching
		$this->response->headers('Content-Type', (string) $mime);
		$this->response->headers('Content-Length', (string) $size);
		$this->response->headers('ETag', $etag);

		// Send the file content as the response
		$this->response->body($contents);
	}

}
