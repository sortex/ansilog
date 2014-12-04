<?php
class Arr extends Kohana_Arr {

	/**
	 * Converts an array to CSV string
	 *
	 * @static
	 * @param  array   $array    Result-set
	 * @param  array   $columns  Array of column definition ( [ fld: '', name: '', type: ''] , [ ... ] )
	 * @return string
	 */
	public static function to_csv(array $array, array $columns = [])
	{
		if (empty($array)) {
			return '';
		}

		// Using all payload columns, as default
		if (empty($columns))
		{
			foreach ($array[0] as $key => $val)
			{
				$columns[] = [
					'fld'  => $key,
					'name' => ucfirst(str_replace('_', ' ', $key)),
					'type' => '',
				];
			}
		}

		// Collect header row
		$header = [];
		foreach ($columns as $column)
		{
			$header[] = '"'.$column['name'].'"';
		}
		$csv = implode(',', $header)."\n";

		// Collect payload data
		foreach ($array as $record)
		{
			$values = [];
			foreach ($columns as $column)
			{
				$val = Arr::get($record, $column['fld'], '');

				// Arrays, objects and resources (Non-scalar) get different treatment
				if ( ! is_scalar($val))
				{
					$val = implode(',', is_array($val) ? $val : get_object_vars($val));
				}

				switch ($column['type'])
				{
					case 'date':
						$val = $val && ctype_digit($val) ? Date::formatted_time('@'.$val) : $val;
						break;
					case 'time':
						$val = $val && ctype_digit($val) ? Date::formatted_time('@'.$val) : $val;
						break;
					case 'datetime':
						$val = $val && ctype_digit($val) ? Date::formatted_time('@'.$val) : $val;
						break;
					case 'checkbox':
						$val = $val ? 'Yes' : 'No';
						break;
					case 'status':
						$val = $val ? 'Active' : 'Not active';
						break;
				}
				$val = '"'.$val.'"';
				$values[] = $val;
			}
			$row = strip_tags(implode(',', $values));

			// Just for hebrew !!! stupid Microsoft control codes
			// http://www.php.net/manual/en/function.iconv.php#71192
			$row = iconv('UTF-8', 'CP1255//TRANSLIT//IGNORE', $row);

			// Fast removal of whitespace inside string
			$csv .= str_replace(str_split("\t\n\r\0\x0B"), '', $row)."\n";
		}
		return $csv;
	}

}
