<?php
namespace Ansilog\Core\Usecase\Page\View;

class Interactor {

	private $page;
	private $viewer;

	public function __construct(Page $page, Viewer $viewer)
	{
		$this->page = $page;
		$this->viewer = $viewer;
	}

	public function interact()
	{
		return $this->page->get_details();
	}
}
