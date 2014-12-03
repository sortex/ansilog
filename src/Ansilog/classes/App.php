<?php
namespace Ansilog;

use Ansilog\Core\Tool\Profiler;
use InvalidArgument;
use Pimple;
use Arr;

/**
 * App container
 */
class App extends Pimple\Container {

	public $auth_user;
	public $site;

	protected $name;
	protected $path;
	protected $environment;
	protected $version;
	protected $revision;
	protected $theme;

	protected $profiler;

	public function __construct($options = NULL)
	{
		$this->version  = Arr::get($options, 'version');
		$this->revision = Arr::get($options, 'revision');
		$this->theme    = Arr::get($options, 'theme');

		// Initialize Pimple\Container
		parent::__construct();
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function getRevision()
	{
		return $this->revision;
	}

	public function getTheme()
	{
		return $this->theme;
	}

	public function setProfiler(Profiler $profiler)
	{
		$this->profiler = $profiler;
		return $this;
	}

	public function setVersion($version)
	{
		$this->version = $version;
		return $this;
	}

	public function setRevision($revision)
	{
		$this->revision = $revision;
		return $this;
	}

	public function setTheme($theme)
	{
		$this->theme = $theme;
		return $this;
	}

	/**
	 * Build a concrete instance of a class
	 *
	 * @param  string  $concrete  The name of the class to build
	 * @return mixed   The instantiated class
	 * @throws InvalidArgument
	 */
	public function build($concrete)
	{
		if ($this->profiler)
		{
			$benchmark = $this->profiler->start('Dependency Injection', $concrete);
		}

		// Get function's arguments and remove the first arg
		$user_args = func_get_args();
		array_shift($user_args);

		$reflection = new \ReflectionClass($concrete);

		if ( ! $reflection->isInstantiable())
			throw new InvalidArgument(
					'Class :name is not instantiable.', [ ':name' => $concrete ]
			);

		$constructor = $reflection->getConstructor();

		if (is_null($constructor))
		{
			return new $concrete;
		}

		$dependencies = $this->get_dependencies($constructor, $user_args);

		$this->profiler AND $this->profiler->stop($benchmark);

		return $reflection->newInstanceArgs($dependencies);
	}

	/**
	 * Recursively collect the dependency list for the provided method
	 * Use user_args array for any extra arguments for method invocation
	 *
	 * @param  \ReflectionMethod  $method     Reflection method to reveal
	 * @param  array              $user_args  Optional arguments for new instance
	 * @return array
	 */
	public function get_dependencies(\ReflectionMethod $method, array $user_args = [])
	{
		if ($this->profiler)
		{
			$benchmark = $this->profiler
				->start('Dependency Injection', $method->class.'::'.$method->getName());
		}

		$dependencies = [];

		foreach ($method->getParameters() as $param)
		{
			if (isset($this[$param->name]))
			{
				// Fetch available DI container item (by param name)
				$dependencies[] = $this[$param->name];
			}
			elseif ($dependency = $param->getClass())
			{
				// If parameter is a class, build it (recursively)
				$dependencies[] = $this->build($dependency->name);
			}
			else
			{
				if ($param->isOptional())
				{
					// Use default optional value
					$dependencies[] = $param->getDefaultValue();
				}
				else
				{
					// Otherwise use provided argument in order
					$dependencies[] = array_shift($user_args);
				}
			}
		}

		$this->profiler AND $this->profiler->stop($benchmark);

		return $dependencies;
	}

}
