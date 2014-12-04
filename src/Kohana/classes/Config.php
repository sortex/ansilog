<?php

class Config extends Kohana_Config {

	/**
	 * Preload groups from a global config file
	 */
	public function preload($filepath, $merge_existing = FALSE)
	{
		// We search from the "lowest" source and work our way up
		$sources = array_reverse($this->_sources);

		$groups = [];
		foreach ($sources as $source)
		{
			if ($source instanceof Kohana_Config_Reader)
			{
				if ($source_config = $source->load($filepath))
				{
					$groups = Arr::merge($groups, $source_config);
				}
			}
		}

		// Iterate through the immediate children keys
		// and set them as new groups in the config container.
		foreach ($groups as $group => $config)
		{
			if ($merge_existing && isset($this->_groups[$group]))
			{
				$config = Arr::merge($this->_groups[$group]->as_array(), $config);
			}
			$this->_groups[$group] = new Config_Group($this, $group, $config);
		}

		return $this;
	}

}
