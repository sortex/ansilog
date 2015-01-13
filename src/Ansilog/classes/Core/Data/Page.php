<?php
namespace Ansilog\Core\Data;

class Page {

	public $id;
	public $grid;
	public $parent;
	public $meta = [ 'keywords' => '', 'description' => '' ];
	public $language;
	public $slug;
	public $title;
	public $subtitle;
	public $content;
	public $modified_at;
	public $deleted_at;
	public $created_at;

}
