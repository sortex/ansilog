<?php
namespace Ansilog\Core\Data;

class User
{
	public $id;
	public $email;
	public $username;
	public $password;
	public $first_name;
	public $middle_name;
	public $last_name;
	public $phone;
	public $mobile;

	/**
		* @var Locale $locale
	 */
	public $locale;

	public $date_format;
	public $timezone;
	public $verified;
	public $modified_at;
	public $deleted_at;
	public $created_at;
}
