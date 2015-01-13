<?php
namespace Ansilog\Core;

class AppTest extends \PHPUnit_Framework_TestCase {

	protected $app;
	protected $options = [
		'name'     => 'blah',
		'version'  => '1.0.0',
		'revision' => 'abced1',
		'theme'    => 'default',
	];

	public function setUp()
	{
		$this->app = new App($this->options);
	}

	public function tearDown()
	{
		unset($this->app);
	}

	public function testCanBeInstantiated()
	{
		$this->assertInstanceOf('Ansilog\Core\App', $this->app);
		$this->assertInstanceOf('Pimple\Container', $this->app);
	}

	public function testGetMetaData()
	{
		$this->assertEquals($this->app->getVersion(), $this->options['version']);
		$this->assertEquals($this->app->getRevision(), $this->options['revision']);
		$this->assertEquals($this->app->getTheme(), $this->options['theme']);
	}

}
