<?php

class Log_Hipchat extends Log_Writer {

	protected $token = '';
	protected $rooms = [];
	protected $from  =  '';
	protected $alert = FALSE;
	protected $color = '';
	protected $type  = 'html';

	protected $format = 'level: body (ip)';
	protected $provider;

	public function __construct(array $options)
	{
		if (empty($options['token']))
			throw new Exception('[Log/HipChat] Missing token');

		if (empty($options['rooms']))
			throw new Exception('[Log/HipChat] Missing rooms');

		$this->token = $options['token'];
		$this->rooms = (array) $options['rooms'];
		$this->from  = Arr::get($options, 'from', 'Log');
		$this->alert = (bool) Arr::get($options,  'alert');
		$this->color = Arr::get($options, 'color') ?: 'red';
		$this->type  = Arr::get($options, 'type') ?: 'html';

		if (isset($options['format']))
		{
			$this->format = $options['format'];
		}
	}

	public function write(array $messages)
	{
		if (empty($this->provider))
		{
			$this->provider = new HipChat\HipChat($this->token);
		}

		foreach ($messages as $message)
		{
			$message['ip'] = Request::$client_ip;

			foreach ($this->rooms as $room)
			{
				$this->provider->message_room(
					$room,
					$this->from,
					$this->format_message($message),
					$this->alert,
					$this->color,
					$this->type
				);
			}
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
