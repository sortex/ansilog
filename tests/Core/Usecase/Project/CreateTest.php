<?php
namespace Ansilog\Core\Usecase\Project;

use Ansilog\Core\Data;
use Ansilog\Core\Repository;

class CreateTest extends \PHPUnit_Framework_TestCase {

	public function testCanBeExecuted()
	{
		$data = [ 'name' => 'foobar', 'title' => 'Foo bar' ];
		$project = new Data\Project;
		$project->name = $data['name'];
		$project->title = $data['title'];

		$repo = $this->getMockBuilder('Ansilog\Core\Repository\Project')
			->disableOriginalConstructor()
			->getMock();

		$repo->expects($this->once())
			->method('hydrate')
			->with(
				$this->equalTo($project),
				$this->equalTo($data)
			);

		$repo->expects($this->once())
			->method('create')
			->with($this->equalTo($project));

		$validate = $this->getMockBuilder('Ansilog\Tool\Validation')
			->getMock();

		$repo->expects($this->once())
			->method('create')
			->with($this->equalTo($project));

		$output = (new Create($project, $repo, $validate))
			->set($data)
			->execute();

		$this->assertEquals($output['name'], $data['name']);
		$this->assertEquals($output['title'], $data['title']);
	}

}
