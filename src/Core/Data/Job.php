<?php
namespace Ansilog\Core\Data;

class Job {

	const STATUS_IN_PROGRESS = 'in_progress';
	const STATUS_STOPPED = 'stopped';
	const STATUS_FAILED = 'failed';
	const STATUS_COMPLETED = 'completed';

	public $id;
	public $user_id;
	public $playbook_id;

	public $status;
	public $version;
	public $output;

	public $hosts = [];
	public $groups = [];
	public $tags = [];
	public $parameters = [];

	public $created_at;
	public $completed_at;

}
