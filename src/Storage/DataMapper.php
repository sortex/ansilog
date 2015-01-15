<?php
namespace Sortex\Storage;

trait DataMapper {

	public function hydrate($object, array $data, callable $filter = NULL)
	{
		foreach ($data as $key => $value)
		{
			// Make sure object has the property
			if (property_exists($object, $key))
			{
				// Run custom filter function
				if ( ! $filter || $filter($key, $value))
				{
					$object->$key = $value;
				}
			}
		}
	}

}
