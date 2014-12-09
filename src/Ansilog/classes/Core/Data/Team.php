<?php
namespace Ansilog\Core\Data;

class Account
{
	public $id;
	public $title;
	public $invoice_title;
	public $contact_user;
	public $phone;
	public $mobile;

	/**
		* @var Address $address
	 */
	public $address;

	public $modified_at;
	public $deleted_at;
	public $created_at;
}

