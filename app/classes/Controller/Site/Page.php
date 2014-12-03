<?php

class Controller_Site_Page extends Controller_Site {

	public function action_index(
		Ansilog\CMS\View\Site\Page $view,
		Ansilog\Core\Usecase\Page\View $usecase,
		Ansilog\CMS\Repository\Page\YAML $repo,
		Ansilog\Core\Data\Page $page,
		Ansilog\Core\Data\User $user
	)
	{
		$user->id = $this->request->param('user_id');
		$page->slug = trim($this->request->param('slug'), '/') ?: 'home';
		$page->viewer = $user;

		$data = $usecase
			->set($page, $repo)
			->fetch()
			->interact();

		$this->template = $data['template'];
		$this->content = $data['content'];
		$this->view = $view->set($data);
	}

}
