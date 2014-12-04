<?php
/**
 * Overriding parent Kohana
 */
abstract class Minion_Task extends Kohana_Minion_Task {

	/**
	 * @var  App
	 */
	protected $app;

	/**
	 * Dependency injected from index.php
	 *
	 * @param  mixed  $app
	 */
	public function app($app)
	{
		$this->app = $app;
	}

	/**
	 * @inheritdoc
	 */
	public static function factory(array $options = array(), $client_params = [])
	{
		if (isset($options['task']) OR isset($options[0]))
		{
			// The first positional argument (aka 0) may be the task name
			$task = Arr::get($options, 'task', $options[0]);

			unset($options['task'], $options[0]);
		}
		else
		{
			// If we didn't get a valid task, generate the help.
			$task = 'help';
		}

		$class = Minion_Task::convert_task_to_class_name($task);

		if ( ! class_exists($class))
		{
			throw new Minion_Task_Exception(
				'Task class `:class` not exists', 
				array(':class' => $class)
			);
		}
		elseif ( ! is_subclass_of($class, 'Minion_Task'))
		{
			throw new Minion_Task_Exception(
				'Class `:class` is not a valid minion task', 
				array(':class' => $class)
			);
		}

		$class = new $class;

		// Show the help page for this task if requested
		if (array_key_exists('help', $options))
		{
			$class->_method = '_help';

			unset($options['help']);
		}

		$class->set_options($options);

		return $class;
	}

	/**
	 * Allows for dependency injection.
	 *
	 * @param   array    $params Params
	 */
	protected function __construct(array $params = [])
	{
		foreach ($params as $key => $value)
		{
			if (method_exists($this, $key))
			{
				$this->$key($value);
			}
		}

		parent::__construct();
	}

}
