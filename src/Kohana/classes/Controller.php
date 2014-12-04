<?php
/**
 * Controller Base
 */
class Controller {

	/**
	 * @var  Request  Request that created the controller
	 */
	public $request;

	/**
	 * @var  Response The response that will be returned from controller
	 */
	public $response;

	/**
	 * @var  App
	 */
	public $app;

	/**
	 * @var array Map of HTTP methods -> actions
	 */
	protected $action_map = [];

	/**
	 * Creates a new controller instance. Each controller must be constructed
	 * with the request object that created it.
	 *
	 * @param   Request   $request  Request that created the controller
	 * @param   Response  $response The request's response
	 * @param   App       $app      The app object
	 */
	public function __construct(Request $request, Response $response, ArrayAccess $app)
	{
		// Assign the request to the controller
		$this->request = $request;

		// Assign a response to the controller
		$this->response = $response;

		// Assigns the app object through the request flow
		$this->app = $app;
	}

	/**
	 * Execute controller action with special abilities:
	 * - Loads auth user
	 * - Determine action verbs by map
	 * - Inject action dependencies
	 *
	 * @param   ReflectionClass $reflection
	 * @throws  mixed
	 * @return  Response
	 */
	public function execute(ReflectionClass $reflection)
	{
		// Execute the "before action" method
		$this->before();

		// Determine the action to use
		if (empty($this->action_map))
		{
			$action = 'action_';
		}
		else
		{
			// Get the basic verb based action
			$action = $this->action_map[$this->request->method()];
		}
		$action .= $this->request->action();

		// If the action doesn't exist, it's a 404
		if ( ! method_exists($this, $action))
		{
			throw HTTP_Exception::factory(404,
				'The requested URL :uri was not found on this server.',
				array(':uri' => $this->request->uri())
			)->request($this->request);
		}

		// Resolve all dependencies for this context
		$deps = $this->app->get_dependencies($reflection->getMethod($action));

		// Execute the action itself,
		// unpack array as arguments.
		$this->{$action}(...$deps);

		// Execute the "after action" method
		$this->after();

		return $this->response;
	}

	/**
	 * Automatically executed before the controller action. Can be used to set
	 * class properties, do authorization checks, and execute other custom code.
	 *
	 * @return  void
	 */
	public function before()
	{
		// Nothing by default
	}

	/**
	 * Automatically executed after the controller action. Can be used to apply
	 * transformation to the response, add extra output, and execute
	 * other custom code.
	 *
	 * @return  void
	 */
	public function after()
	{
		// Nothing by default
	}

	/**
	 * Issues a HTTP redirect.
	 *
	 * Proxies to the [HTTP::redirect] method.
	 *
	 * @param  string  $uri   URI to redirect to
	 * @param  int     $code  HTTP Status code to use for the redirect
	 * @throws HTTP_Exception
	 */
	public static function redirect($uri = '', $code = 302)
	{
		HTTP::redirect( (string) $uri, $code);
	}

	/**
	 * Checks the browser cache to see the response needs to be returned,
	 * execution will halt and a 304 Not Modified will be sent if the
	 * browser cache is up to date.
	 *
	 *     $this->check_cache(sha1($content));
	 *
	 * @param  string  $etag  Resource Etag
	 * @return Response
	 */
	protected function check_cache($etag = NULL)
	{
		return HTTP::check_cache($this->request, $this->response, $etag);
	}

}
