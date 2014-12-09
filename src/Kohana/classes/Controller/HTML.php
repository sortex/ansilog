<?php
abstract class Controller_HTML extends Controller {

	/**
	 * @var  mixed  The content View object
	 */
	protected $view;

	/**
	 * @var  string  Formatted content
	 */
	protected $content = '';

	/**
	 * @var  string  Content's template
	 */
	protected $template = NULL;

	/**
	 * Child controllers must use render traits
	 */
	abstract protected function render($template, $content);

	/**
	 * AFTER - render view
	 *
	 * @throws HTTP_Exception
	 * @throws Kohana_View_Exception
	 */
	public function after()
	{
		// If content is NULL, no View to render
		if ($this->view === NULL)
		{
			// Verify if response was handled manually
			if ( ! $this->response->body())
				throw new Kohana_View_Exception('An empty view can\'t fulfill request');
		}
		elseif (is_object($this->view))
		{
			$this->response->body(
				$this->render($this->template, $this->content)
			);
		}
	}

	public function assets()
	{
		$escaped = 'window.pass = '.json_encode($this->environment()).';';
		$assets = '<script type="text/javascript">'.$escaped.'</script>'.PHP_EOL;

		return $assets;
	}

	/**
	 * Returns environment information
	 */
	public function environment()
	{
		return [
			'route' => [
				'name'       => Route::name($this->request->route()),
				'directory'  => $this->request->directory(),
				'controller' => strtolower($this->request->controller()),
				'action'     => $this->request->action()
			],
			'url' => [
				'full'   => URL::base('http'),
				'base'   => URL::base(),
				'upload' => Kohana::$config->load('app')->upload_uri,
				'media'  => URL::base().Media::uri('/').'/',
			],
			'lang' => [
				'current' => I18n::$lang
			],
			'environment' => ENVNAME,
		];
	}

}
