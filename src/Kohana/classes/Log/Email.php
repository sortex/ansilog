<?php

class Log_Email extends Log_Writer {

	protected $from;
	protected $subject;
	protected $to;
	protected $cc;

	protected $format   = 'level: body (ip)';
	protected $template = 'email/error';

	public function __construct(array $options)
	{
		if (empty($options['from']))
			throw new Exception('[Log/Email] Missing sender/from');

		if (empty($options['subject']))
			throw new Exception('[Log/Email] Missing subject');

		if (empty($options['to']))
			throw new Exception('[Log/Email] Missing recipient');

		$this->from = $options['from'];
		$this->subject = $options['subject'];
		$this->to = $options['to'];
		$this->cc = Arr::get($options, 'cc') ?: [];

		if (isset($options['format']))
		{
			$this->format = $options['format'];
		}

		if (isset($options['template']))
		{
			$this->template = $options['template'];
		}
	}

	public function write(array $messages)
	{
		$request = Request::$initial;

		$params = [
			'site'    => '',
			'version' => '',
			'environment' => '',
			'error'   => '',
			'ip'      => Request::$client_ip,
			'url'     => $request ? $request->url('http') : '',
			'get'     => $request ? Debug::vars($request->query()) : '',
			'post'    => $request ? Debug::vars($request->post()) : '',
			'server'  => Debug::vars($_SERVER)
		];

		foreach ($messages as $message)
		{
			$message['body'] = $this->format_message($message);

			if (isset($message['additional']['exception']))
			{
				$message['error'] =
				 	Kohana_Exception::text($message['additional']['exception']);
			}

			if (isset($message['additional']['exception']))
			{
				$message['exception'] = $message['additional']['exception'];
				unset($message['additional']['exception']);
			}

			$message = Arr::merge($params, $message);
			$macros = [];
			foreach ($message as $key => $val)
			{
				if (is_scalar($val))
				{
					$macros[':'.$key] = $val;
				}
			}

			$subject = strtr($this->subject, $macros);
			$from = (array) $this->from;
			foreach ($from as $entry_index => & $entry_name)
			{
				// Checking if entry is a key/value or is it an index array
				if (is_string($entry_index))
				{
					$entry_name = strtr($entry_name, $macros);
				}
			}
			unset($entry_name);

			$content = View::factory($this->template, $message)->render();

			$email = Shadowhand\Email::factory($subject)
				->message($content, 'text/html')
				->from($from)
				->to($this->to)
				->cc($this->cc)
				->send();
		}
	}

	/**
	 * Overriding to support configurable format
	 */
	public function format_message(array $message, $format = '')
	{
		return parent::format_message($message, $this->format);
	}

}
