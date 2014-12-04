<?php
/**
 * Overriding Kohana's HTML for media uri
 */
class HTML extends Kohana_HTML {

	/**
	 * Creates a script link.
	 *
	 * @see Kohana_HTML::script
	 */
	public static function script($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		// Adding the cache buster prefix.  See kohana-media module
		if (strpos($file, '://') === FALSE)
		{
			$file = Media::uri($file);
		}
		return parent::script($file, $attributes, $protocol, $index);
	}

	/**
	 * Creates a style sheet link element.
	 *
	 * @see Kohana_HTML::style
	 */
	public static function style($file, array $attributes = NULL, $protocol = NULL, $index = FALSE)
	{
		// Adding the cache buster prefix.  See kohana-media module
		if (strpos($file, '://') === FALSE)
		{
			$file = Media::uri($file);
		}
		return parent::style($file, $attributes, $protocol, $index);
	}

}
