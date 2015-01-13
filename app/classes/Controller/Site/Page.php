<?php

class Controller_Site_Page extends Controller_Site {

	public function action_index(
		Ansilog\Core\Usecase\Page\View $usecase,
		Ansilog\CMS\View\Site\Page $view
	)
	{
		$slug = $this->request->param('slug');
		$user_id = $this->request->param('user_id');

		$data = $usecase
			->set($slug, $user_id)
			->execute();

		$this->template = $data['template'];
		$this->content = $data['content'];
		$this->view = $view->set($data);
	}

}
