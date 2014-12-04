<?php
//use Mustache_Engine;
//use Parsedown;

trait Ability_Mustache {

	/**
	 * @var  string  $grid  The grid's content string itself
	 */
	protected $grid = '';

	/**
	 * @var  array  $areas  Page area aliases
	 */
	protected $areas = [];

	/**
	 * Implement render
	 */
	protected function render($template, $content)
	{
		$mustache = new Mustache_Engine([
			'cache' => DOCROOT.'var/cache/'.APPNAME.'/mustache',
			'loader' => new Mustache_Loader_Kohana,
			'partials_loader' => new Mustache_Loader_Kohana_Alias,
			'pragmas' => [ Mustache_Engine::PRAGMA_BLOCKS ],
			'escape' => function ($value) {
				return HTML::chars($value);
			},
			'helpers' => [
				'url_base'    => URL::base(),
				'url_media'   => URL::base().Media::uri('/').'/'
			],
			'partials' => [
				'environment' => function () {
					return $this->assets();
			 	},
			]
		]);

			$mustache->getPartialsLoader()
				->setStringTemplates([
					'content' => (new Parsedown)->text($content)
				]);

			//if ($mustache->getPartialsLoader() instanceof Mustache\Alias)
			//{
				//$content = strtolower($template);
			//}
			//else
			//{
				//// Load template into mustache's loader
				//$content = $mustache->getLoader()->load($template);
			//}

			// Merge partials with main content partials for this page
			//$mustache->setPartials(
				//Arr::merge($this->partials, [ self::CONTENT_PARTIAL => $content ])
			//);

			$response = $mustache->render($template, $this->view);

		return $response;
	}

}
