<?php
namespace spec\Ansilog;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AppSpec extends ObjectBehavior
{
	protected $options = [
		'name'     => 'blah',
		'version'  => '1.0.0',
		'revision' => 'abced1',
		'theme'    => 'default',
	];

	function let()
	{
		$this->beConstructedWith($this->options);
	}

	function it_is_initializable()
	{
		$this->shouldHaveType('Ansilog\App');
		$this->shouldBeAnInstanceOf('Pimple\Container');
	}

	function it_sets_up_correctly()
	{
		$this->getVersion()->shouldEqual($this->options['version']);
		$this->getRevision()->shouldEqual($this->options['revision']);
		$this->getTheme()->shouldEqual($this->options['theme']);
	}

}
