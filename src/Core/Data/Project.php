<?php
namespace Ansilog\Core\Data;

class Project {

	public $id;
	public $name;
	public $title;

	public function rules()
	{
		return [

			'title' => [
				[ 'not_empty' ],
				[ 'max_length', [ ':value', 255 ] ]
			],

		];
	}

}
