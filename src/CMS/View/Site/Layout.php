<?php
namespace Sortex\CMS\View\Site;

use Ansilog\Assets;
use I18n;

/**
 * Site layout view
 */
class Layout {

	/**
	 * @var array Current site
	 */
	public $site;

	public $partials = [];

	/**
	 * Assigns asset groups this view depends on
	 *
	 * @param  Assets  $assets  the Assets object
	 * @return self
	 */
	public function assets(Assets $assets)
	{
		$assets->group('layout');

		return $this;
	}

	/**
	 * i18n helper for mustache
	 *
	 * @return callable
	 */
	public function __()
	{
		return function($string)
		{
			return __($string);
		};
	}

}
