<?php
/**
 * Tree-based Valve KeyValues parser
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class KeyValues
{
	/**
	 * @var array Key values data
	 */
	protected $_data = array();
	
	/**
	 * @var string Root section name
	 */
	protected $_name;
	
	
	/**
	 * Constructor
	 * 
	 * @param string $name Root section name
	 * @param array $data Optional key values data
	 */
	function __construct($name, $data = null)
	{
		$this->_name = $name;
		
		if(is_array($data))
		{
			$this->_data = $data;
		}
	}
	
	/**
	 * Returns a property value
	 * 
	 * @param string $name The property name
	 * @return mixed The property value
	 */
	public function __get($name)
	{
		switch($name)
		{
			case 'name':
				return $this->_name;
		}
	}
	
	/**
	 * Blocks setting property values
	 * 
	 * @param string $name The property name
	 * @param string $value The property value
	 */
	public function __set($name, $value) {}
	
	/**
	 * Serializes key values data and root section name
	 */
	public function __sleep()
	{
		return array('_data', '_name');
	}
	
	/**
	 * Returns a string representation of the data
	 * 
	 * @return string The string representation of the data
	 */
	public function __toString()
	{
		$ret  = $this->name . "\n{\n";
		$ret .= $this->_build($this->_data);
		$ret .= '}';
		
		return $ret;
	}
	
	
	/**
	 * Loads key values data from file
	 * 
	 * @param string $file File to load from
	 * @return boolean Whether loading was successful
	 */
	public function load($file)
	{
		if(!is_readable($file))
			return false;
		
		// Use token_get_all() to easily ignore comments and whitespace
		$tokens      = token_get_all("<?php\n" . file_get_contents($file) . "\n?>");
		$this->_data = $this->_parse($tokens);
		
		// Strip root section
		$this->_data = reset($this->_data);
		return true;
	}
	
	/**
	 * Saves key values data to file
	 * 
	 * @param string $file File to save to
	 * @return boolean Whether saving was successful
	 */
	public function save($file)
	{
		if(($fp = fopen($file, 'w+')) === false)
			return false;
		
		fwrite($fp, $this);
		fclose($fp);
		return true;
	}
	
	/**
	 * Returns an array representation of the data
	 * 
	 * @return array The array representation of the data
	 */
	public function toArray()
	{
		return $this->_data;
	}
	
	
	/**
	 * Recursively builds key values string
	 * 
	 * @param array $data Key values data to write
	 * @param integer $level Optional level to use for indenting
	 */
	private function _build($data, $level = null)
	{
		// Default level to 0
		if(!is_numeric($level))
		{
			$level = 0;
		}
		
		$indent = str_repeat("\t", $level + 1);
		$ret    = '';
		
		foreach($data as $key => $value)
		{
			// Write key value pair
			if(is_string($value))
			{
				$ret .= sprintf("%s\"%s\"\t\"%s\"\n",
					$indent, $key, $value);
			}
			else if(is_array($value))
			{
				reset($value);
				// If array is numerical, write key sub-value pairs
				if(is_int(key($value)))
				{
					foreach($value as $sub_value)
					{
						$ret .= sprintf("%s\"%s\"\t\"%s\"\n",
							$indent, $key, $sub_value);
					}
				}
				// Otherwise, recursively write section
				else
				{
					$ret .= sprintf("%s\"%s\"\n%s{\n",
						$indent, $key, $indent);
					
					$ret .= $this->_build($value, $level + 1);
					$ret .= $indent . "}\n";
				}
			}
		}
		
		return $ret;
	}
	
	/**
	 * Recursively parses key values data from tokens
	 * 
	 * @param array $tokens Tokens received from token_get_all()
	 */
	private function _parse(&$tokens)
	{
		$data = array();
		$key  = null;
		
		// Use each() so the array cursor is also advanced
		// when the function is called recursively
		while(list(, $token) = each($tokens))
		{
			// New section
			if($token == '{')
			{
				// Recursively parse section
				$data[$key] = $this->_parse($tokens);
				$key        = null;
			}
			// End section
			else if($token == '}')
			{
				return $data;
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
							// If value is already set, treat as an array
							// to allow multiple values per key
							if(isset($data[$key]))
							{
								// If value is not an array, cast
								if(!is_array($data[$key]))
								{
									$data[$key] = (array)$data[$key];
								}
								
								// Add value to array
								$data[$key][] = $value;
							}
							// Otherwise, store key value pair
							else
							{
								$data[$key] = $value;
							}
							
							$key = null;
						}
				}
			}
		}
		
		return $data;
	}
}