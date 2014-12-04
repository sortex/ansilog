<?php
namespace Ansilog\Core\Usecase\Page\View;

use Ansilog\Core\Data;
use Ansilog\Core\Exception;

class Viewer extends Data\User {

	public $id;
	private $repository;

	public function __construct(Data\User $user, Repository $repository)
	{
		$this->id = $user->id;
		$this->repository = $repository;
	}

}
