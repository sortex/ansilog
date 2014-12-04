<?php
/**
 * Class Mustache_Loader_Kohana
 *
 * @package    Kostache
 * @category   Base
 * @author     Jeremy Bush <jeremy.bush@kohanaframework.org>
 * @copyright  (c) 2010-2012 Jeremy Bush
 * @license    MIT
 */
class Mustache_Loader_Kohana
	implements Mustache_Loader, Mustache_Loader_MutableLoader {

	private $_base_dir = 'media/templates';
	private $_extension = 'mustache';
	private $_templates = [];

	/**
	 * Allows resetting the base dir and options
	 *
	 * @param   null   $base_dir  Base directory, overrides default
	 * @param   array  $options   Options: [ extension ]
	 */
	public function __construct($base_dir = NULL, $options = array())
	{
		if ($base_dir)
		{
			$this->_base_dir = $base_dir;
		}

		if (isset($options['extension']))
		{
			$this->_extension = ltrim($options['extension'], '.');
		}
	}

	/**
	 * Loads a template from a file-path
	 *
	 * @param   string  $name  Template alias
	 * @return  string
	 */
	public function load($name)
	{
		if (is_object($name) && $name instanceof Closure)
			return $name();

		// TODO
		if (strpos($name, 'widget.') === 0)
			return '';

		if ( ! isset($this->_templates[$name]))
		{
			$this->_templates[$name] = $this->_load_file($name);
		}

		return $this->_templates[$name];
	}

	/**
	 * Loads a template file utilizing Kohana's CFS
	 *
	 * @param   string  $file_path  Template's file-path
	 * @return  string
	 * @throws  Kohana_Exception
	 */
	protected function _load_file($file_path)
	{
		$filename = Kohana::find_file($this->_base_dir, strtolower($file_path), $this->_extension);

		if ( ! $filename)
			throw new Kohana_Exception('Mustache template ":name" not found',
				[':name' => $this->_base_dir.'/'.strtolower($file_path)]
			);

		return file_get_contents($filename);
	}

	/**
	 * Set an associative array of Template sources for this loader
	 *
	 * @param   array  $templates  An array of templates, key/value: alias=>source
	 */
	public function setTemplates(array $templates)
	{
		$this->_templates = array_merge($this->_templates, $templates);
	}

	/**
	 * Set a Template source by name
	 *
	 * @param   string  $name      Template alias
	 * @param   string  $template  Mustache Template source
	 */
	public function setTemplate($name, $template)
	{
		$this->_templates[$name] = $template;
	}

}
