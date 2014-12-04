<?php
namespace Ansilog\CMS\Repository\Page;

use Ansilog\Core\Usecase\Page;
use Symfony\Component\Yaml\Yaml as Yaml_Engine;

use URL, Arr;

class YAML implements Page\View\Repository {

	public function read_page_details($slug, $path = NULL)
	{
		// TODO: APPPATH no good
		isset($path) OR $path = APPPATH.'pages/';

		// TODO: Use Tool\URL
		// Load and process content and its YAML matter
		$slug = URL::title($slug);
		$path = $path.$slug.'.md';
		$content = file_get_contents($path);
		list($data, $content) = $this->parse_yaml($content);
		$data['slug'] = $slug;
		$data['content'] = $content;

		$stat = stat($path);
		$data['modified_at'] = Arr::get($data, 'modified', $stat['mtime']);
		$data['created_at'] = Arr::get($data, 'created', $stat['ctime']);

		return $data;
	}

	public function create_page_details($slug, $content)
	{
		return [];
	}

	/**
	 * Parses a string for YAML matter and return a list
	 * with the YAML keys and filtered content.
	 */
	private function parse_yaml($str)
	{
		$args = [];
		if (strpos($str, '---'.PHP_EOL, 0) !== FALSE)
		{
			$i = 4;
			$len = strlen($str);
			while ($i < $len - 1)
			{
				$eol = strpos($str, PHP_EOL, $i);
				if ($eol - $i === 3 && substr($str, $i, $eol - $i) == '---')
				{
					$yaml = substr($str, 0, $i);
					$args = Yaml_Engine::parse($yaml);
					$str = substr($str, $i + 4);
					break;
				}
				$i = $eol + 1;
			}
		}
		return [ $args, $str ];
	}

}
