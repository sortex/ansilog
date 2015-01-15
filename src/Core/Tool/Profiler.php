<?php
namespace Ansilog\Core\Tool;

interface Profiler {

	public function start($group, $name);
	public function stop($token);

//	public function delete($token);
//	public function groups();
//	public function stats(array $tokens);
//	public function group_stats($groups = NULL);
//	public function total($token);
//	public function application();

}
