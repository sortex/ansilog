<?php

class Log_Sentry extends Log_Writer {

	protected $dsn = '';

	protected $format = 'level: body (ip)';
	protected $provider;

	public function __construct(array $options)
	{
		if (empty($options['dsn']))
			throw new Exception('[Log/Sentry] Missing DSN');

		$this->dsn = $options['dsn'];	

		if (isset($options['format']))
		{
			$this->format = $options['format'];
		}
	}

	public function write(array $messages)
	{
		if (empty($this->provider))
		{
			$this->provider = new Raven_Client($this->dsn);
		}

		$ids = [];
		foreach ($messages as $message)
		{
			$message['ip'] = Request::$client_ip;

			if (isset($message['additional']['exception']))
			{
				$exception = $message['additional']['exception'];
				unset($message['additional']['exception']);

				$info = Arr::merge(
					$this->format_info($exception), $message['additional']);

				$payload = $this->provider->captureException(
					$exception,
					[ 'extra' => $info ]
				);
			}
			else
			{
				$level = $this->_log_levels[$message['level']];
				$payload = $this->provider->captureMessage(
					$this->format_message($message)
				);
			}

			$ids[] = $this->provider->getIdent($payload);

			if ($client->getLastError() !== NULL)
				throw new Kohana_Exception($client->getLastError());
		}

		return $ids;
	}

	/**
	 * Overriding to support configurable format
	 */
	public function format_message(array $message, $format = '')
	{
		return parent::format_message($message, $this->format);
	}

	public function format_info(Exception $exception)
	{
		$info = [
			'ip'      => Request::$client_ip,
			'request' => Request::$current ? Request::$current->body() : ''
		];

		if ($exception instanceof Validation_Exception)
		{
			$info['errors'] = $exception->array->errors(FALSE);
		}

		return $info;
	}

}
