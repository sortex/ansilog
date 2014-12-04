<?php
namespace Ansilog\CMS\View\Site;

use Arr;

class Page {

	public $title = '';
	public $meta = [];

	protected $page = [];

	public function set($page)
	{
		$this->page = Arr::extract(
			$page,
			[ 'title', 'summary', 'meta', 'slug' ]
		);

		$this->title = $this->page['title'];
		$this->meta = $this->page['meta'];

		$this->page = $page;

		// Allow chaining
		return $this;
	}

	public function page()
	{
		return $this->page;
	}

}
