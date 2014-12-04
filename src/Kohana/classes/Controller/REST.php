<?php
/**
 * REST base controller
 */
abstract class Controller_REST extends Controller {

	/**
	 * @var array Request Payload
	 */
	protected $_request_payload = [];

	/**
	 * @var array Response Payload
	 */
	protected $_response_payload = [];

	/**
	 * @var array Response Metadata
	 */
	protected $_response_metadata = [ 'error' => FALSE ];

	/**
	 * @var string Response format (xml/json/csv)
	 */
	protected $_response_format = 'application/json';

	/**
	 * @var array Response Links
	 */
	protected $_response_links = [];

	/**
	 * @var array Map of HTTP methods -> actions
	 */
	protected $action_map = array
	(
		Http_Request::POST   => 'post',
		Http_Request::GET    => 'get',
		Http_Request::PUT    => 'put',
		Http_Request::DELETE => 'delete',
		// Re-route patch requests to put
		'PATCH'              => 'put',
	);

	/**
	 * @var array List of HTTP methods which support body content
	 */
	protected $_methods_with_body_content = array
	(
		Http_Request::POST,
		Http_Request::PUT,
		Http_Request::DELETE,
		'PATCH'
	);

	/**
	 * @var array List of HTTP methods which may be cached
	 */
	protected $_cacheable_methods = array
	(
		Http_Request::GET,
	);

	/**
	 * BEFORE:
	 * - Sets default content Accept type
	 * - Parse request
	 */
	public function before()
	{
		// Strictly applying content accept type
		// The format will be computed in $this->_prepare_response
		$this->request->headers('Accept', $this->_response_format);

		$this->_parse_request();
	}

	/**
	 * AFTER:
	 * - Prepare response
	 */
	public function after()
	{
		$this->_prepare_response();
	}

	/**
	 * Parses the request
	 */
	protected function _parse_request()
	{
		// Override the method if needed.
		$this->request->method(
			$this->request->headers('X_HTTP_METHOD_OVERRIDE') ?: $this->request->method()
		);

		// Is that a valid method?
		if ( ! isset($this->action_map[$this->request->method()]))
		{
			// TODO .. add to the if (maybe??) .. method_exists($this, 'action_'.$this->request->method())
			throw new HTTP_Exception_405('The :method method is not supported. Supported methods are :allowed_methods', [
				':method'          => $this->request->method(),
				':allowed_methods' => implode(', ', array_keys($this->action_map)),
			]);
		}

		// Are we be expecting body content as part of the request?
		if (in_array($this->request->method(), $this->_methods_with_body_content))
		{
			$this->_parse_request_body();
		}
	}

	/**
	 * Parses the request body
	 *
	 * @todo Support more than just JSON - xml!
	 */
	protected function _parse_request_body()
	{
		if (strpos($this->request->headers('content-type'), 'multipart') !== FALSE)
		{
			if (Request::post_max_size_exceeded())
			{
				// Clean the output buffer if one exists
				ob_get_level() AND ob_clean();
				header('Content-Type: application/json', TRUE, 413);
				echo('{ "metadata": { "error": true }, "payload": { "message": "Uploaded file is too large" }}');
				exit(1);
			}

			// Merge POST and FILES key/value pairs
			$this->_request_payload = Arr::merge($this->request->post(), $_FILES);

			// Do not parse multi-part
			return;
		}

		if ($this->request->body() == '')
			return;

		try
		{
			$this->_request_payload = json_decode($this->request->body(), TRUE);

			if ( ! is_array($this->_request_payload) AND ! is_object($this->_request_payload))
				throw new HTTP_Exception_400('Invalid json supplied. ":json"', [
					':json' => $this->request->body(),
				]);
		}
		catch (Exception $e)
		{
			throw new HTTP_Exception_400('Invalid json supplied. ":json"', [
				':json' => $this->request->body(),
			]);
		}
	}

	/**
	 * Prepares response
	 */
	protected function _prepare_response()
	{
		// Should we prevent this request from being cached?
		if ( ! in_array($this->request->method(), $this->_cacheable_methods))
		{
			$this->response->headers('cache-control', 'no-cache, no-store, max-age=0, must-revalidate');
		}

		if (strpos($this->request->headers('content-type'), 'multipart') !== FALSE)
		{
			// Multi-part response headers
			$this->response->headers([
					'vary'                   => 'Accept',
					'pragma'                 => 'no-cache',
					'content-type'           => 'application/json',
					'cache-control'          => 'no-store, no-cache, must-revalidate',
					'content-disposition'    => 'inline; filename="files.json"',
					// Prevent Internet Explorer from MIME-sniffing the content-type:
					'x-content-type-options' => 'nosniff'
				]);
		}

		$this->_prepare_response_body();
	}

	/**
	 * Prepares response body according to requested output format (json/xml/csv)
	 */
	protected function _prepare_response_body()
	{
		$format = $this->request->param('format') ?: $this->request->query('format');

		// Use the default response format
		empty($format)
			AND $format = explode('/', $this->_response_format)[1];

		try
		{
			switch ($format)
			{
				case 'json':
					// Set the correct content-type header
					$this->response->headers('Content-Type', 'application/json');

					$response = array (
						'metadata' => $this->_response_metadata,
						'links'    => $this->_response_links,
						'payload'  => $this->_response_payload
					);

					// Format the response as JSON
					$this->response->body(json_encode($response));
					break;

				case 'xhtml+xml':
				case 'xml':
					// Construct an XML document
					$xml = new DOMDocument('1.0', Kohana::$charset);
					$root = $xml->createElement('response');
					// Meta data
					$metadata = $xml->createElement('metadata');
					foreach ($this->_response_metadata as $key => $val)
					{
						$metadata->appendChild($xml->createElement($key, $val));
					}
					$root->appendChild($metadata);

					// Payload
					$payload = $xml->createElement('payload');
					foreach ($this->_response_payload ?: [] as $item)
					{
						$record = $xml->createElement('record');
						foreach ($item as $key => $val)
						{
							$record->appendChild($xml->createElement($key, $val));
						}
						$payload->appendChild($record);
					}
					$root->appendChild($payload);
					$xml->appendChild($root);

					$xml = $xml->saveXML();

					$this->response->body($xml);
					$this->response->headers('Content-Type',   'text/xml');
					$this->response->headers('Content-Length', strlen($xml));
					break;

				case 'csv':
					// Collect a CSV comma delimited string out of payload
					// Check for specific columns requested
					if ($columns = $this->request->query('columns'))
					{
						$columns = json_decode($columns, TRUE);
					}

					$csv = Arr::to_csv($this->_response_payload ?: [], $columns ?: []);

					// Compress with gzip is browser accepts
					if ($this->request->headers()->accepts_encoding_at_quality('gzip'))
					{
						ob_start('ob_gzhandler');
					}

					$filename = $this->request->query('fn') ?: $this->request->controller();

					$this->response->body($csv);
					$this->response->headers('Content-Disposition', 'attachment; filename="'.$filename.'.csv"');
					$this->response->headers('Content-Length',      strlen($csv));
					$this->response->headers('Content-Type',        'text/csv; charset='.Kohana::$charset);
					$this->response->headers('Pragma',              'no-cache');
					$this->response->headers('Expires',             '0');
					break;
			}
		}
		catch (Exception $e)
		{
			Kohana::$log->add(Log::ERROR, 'Error while formatting response: '.$e->getMessage());
			throw new HTTP_Exception_500('Error while formatting response');
		}
	}

	/**
	 * Generate links
	 *
	 * @param  string  $method
	 * @param  string  $uri
	 * @param  string  $type
	 * @param  array   $parameters
	 * @return array
	 */
	protected function _generate_link($method, $uri, $type, $parameters = NULL)
	{
		$link = [
			'method'     => $method,
			'url'        => $uri,
			'type'       => $type,
			'parameters' => [],
		];

		if ($parameters !== NULL)
		{
			foreach ($parameters as $search => $replace)
			{
				if (is_numeric($search))
				{
					$link['parameters'][':'.$replace] = $replace;
				}
				else
				{
					$link['parameters'][$search] = $replace;
				}
			}
		}

		return $link;
	}

}
