<?php
namespace Tool\Validation;

interface API {

	public function set(array $array, array $rules = []);
	public function rule($field, $rule, array $params = NULL);
	public function rules($field, array $rules);
	public function bind($key, $value = NULL);
	public function check();
	public function error($field, $error, array $params = NULL);
	public function errors($file = NULL, $translate = TRUE);

}
