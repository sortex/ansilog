<?php
namespace Ansilog\Core\Usecase\Page;

use Ansilog\Core\Data;
use Sortex\CMS\Repository;

class Read {

	private $page;
	private $user;
	private $repo;
	private $slug;
	private $user_id;

	public function __construct(
		Data\Page $page,
		Data\User $user,
		Repository\Page\YAML $repo
	)
	{
		$this->page = $page;
		$this->user = $user;
		$this->repo = $repo;
	}

	public function set($slug, $user_id)
	{
		$this->slug = trim($slug, '/') ?: 'home';
		$this->user_id = (int) $user_id;

		return $this;
	}

	public function execute()
	{
		$page_data = $this->repo->read_page_details($this->slug);

		return $page_data;
	}

}
