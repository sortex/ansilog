<?php
namespace Ansilog\Core\Usecase\Page\View;

interface Repository
{
	public function read_page_details($slug, $path = NULL);
	public function create_page_details($slug, $content);

	//public function update_page_details($slug, $content);
	//public function delete_page_details($slug);

	//public function does_page_have_parent($slug);
	//public function does_page_have_children($slug);
}
