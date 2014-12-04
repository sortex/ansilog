<?php

$output = [
	'metadata' => [
		'error' => TRUE,
		'type'  => trim(str_ireplace('Kohana', '', get_class($e)), '_')
	],
	'payload' => [
		'code'    => $code,
		'errors'  => $errors,
		'message' => $message
	]
];

if (Kohana::$environment == Kohana::DEVELOPMENT)
{
	$output['payload']['stacktrace'] = $trace;
}

echo json_encode($output);
