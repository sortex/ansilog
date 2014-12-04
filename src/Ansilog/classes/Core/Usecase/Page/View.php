<?php
namespace Ansilog\Core\Usecase\Page;

use Ansilog\Core\Data\Page;

class View
{
	private $data;
	private $repository;

	public function set(
		Page $page,
		View\Repository $repository
	)
	{
		$this->data = $page;
		$this->repository = $repository;
		return $this;
	}

	public function fetch()
	{
		return new View\Interactor(
			$this->get_page(),
			$this->get_viewer()
		);
	}

	private function get_page()
	{
		return new View\Page(
			$this->data,
			$this->repository
		);
	}

	private function get_viewer()
	{
		return new View\Viewer(
			$this->data->viewer,
			$this->repository
		);
	}
}
