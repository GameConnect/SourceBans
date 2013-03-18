<?php
/**
 * Event-based SourceMod Config parser
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
require_once 'ISMCListener.php';


class SMCParser
{
	/**
	 * Parses a SourceMod Config file
	 * 
	 * @param string $file File to parse
	 * @param ISMCListener $listener Listener to use for callbacks
	 * @return boolean Whether the parsing was successful
	 */
	public static function parse($file, ISMCListener $listener)
	{
		if(!is_readable($file))
			return false;
		
		// Use token_get_all() to easily ignore comments and whitespace
		$tokens = token_get_all("<?php\n" . file_get_contents($file) . "\n?>");
		$level  = 0;
		$key    = null;
		
		foreach($tokens as $token)
		{
			// New section
			if($token == '{')
			{
				if($level++ && $listener->NewSection($key) === false)
					return false;
				
				$key = null;
			}
			// End section
			else if($token == '}')
			{
				if(--$level && $listener->EndSection() === false)
					return false;
			}
			// Key or value
			else
			{
				$value = $token[1];
				switch($token[0])
				{
					case T_CONSTANT_ENCAPSED_STRING:
						// Strip surrounding quotes, then parse as a string
						$value = substr($value, 1, -1);
					case T_STRING:
						// If key is not set, store
						if(is_null($key))
						{
							$key = $value;
						}
						// Otherwise, it's a key value pair
						else
						{
							if($listener->KeyValue($key, $value) === false)
								return false;
							
							$key = null;
						}
				}
			}
		}
		
		return true;
	}
}