<?php
/**
 * Alias mustache partial loader
 */
class Mustache_Loader_Kohana_Alias extends Mustache_Loader_Kohana {

	protected $aliases;

	/**
	 * AliasLoader constructor.
	 *
	 * @param string $baseDir  Base directory containing Mustache Template files.
	 * @param array  $aliases  Associative array of Template aliases (default: array())
	 * @param array  $options  Array of FilesystemLoader options (default: array())
	 */
	public function __construct($baseDir = NULL, array $aliases = array(), array $options = array())
	{
		parent::__construct($baseDir, $options);
		$this->setTemplates($aliases);
	}

	/**
	 * Load a Template by alias or name.
	 *
	 * @param string $name
	 *
	 * @return string Mustache Template source
	 */
	public function load($name)
	{
		if (array_key_exists($name, $this->aliases))
		{
			$name = $this->aliases[$name];
		}

		return parent::load($name);
	}

	public function setStringTemplates(array $templates)
	{
		return parent::setTemplates($templates);
	}

	/**
	 * Set an associative array of Template aliases for this loader.
	 *
	 * @param array $templates
	 */
	public function setTemplates(array $templates)
	{
		$this->aliases = $templates;
	}

	/**
	 * Set a Template alias by name.
	 *
	 * @param string $name
	 * @param string $template Mustache Template alias
	 */
	public function setTemplate($name, $template)
	{
		$this->aliases[$name] = $template;
	}

}
