<?php
namespace Tool\Profiler;

use Ansilog\Core;
use Profiler;

class Kohana implements Core\Tool\Profiler {

	public function start($group, $name)
	{
		return Profiler::start($group, $name);
	}

	public function stop($token)
	{
		return Profiler::stop($token);
	}

}
