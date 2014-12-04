<?php
namespace Ansilog\Core\Usecase\Page\View;

use Ansilog\Core\Data;

class Page extends Data\Page {

	public $slug;

	private $repository;

	public function __construct(Data\Page $page, Repository $repository)
	{
		$this->slug = $page->slug;
		$this->repository = $repository;
	}

	public function get_details()
	{
		return $this->repository->read_page_details($this->slug);
	}

}
