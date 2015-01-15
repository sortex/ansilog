<?php
namespace Ansilog\Core\Usecase\Project;

use Ansilog\Core\Data;
use Ansilog\Core\Repository;
use Tool\Event;
use Tool\Validation;

class Create {

	private $project;
	private $repo;
	private $data;
	private $event;
	private $validate;

	public function __construct(
		Data\Project $project,
		Repository\Project $repo,
		Event\API $event,
		Validation\API $validate
	)
	{
		$this->project = $project;
		$this->repo = $repo;
		$this->event = $event;
		$this->validate = $validate;
	}

	public function set($data)
	{
		$this->data = $data;

		return $this;
	}

	public function execute()
	{
		$this->repo->hydrate($this->project, $this->data);

		$this->event->trigger('project:before:submit', $this->project);

		$this->validate
			->set($this->project, $this->project->rules())
			->check();

		$this->repo->create($this->project);

		$this->event->trigger('project:after:submit', $this->project);

		// Return simple data fields
		return get_object_vars($this->project);
	}

}
