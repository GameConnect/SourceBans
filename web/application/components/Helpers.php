<?php
class Helpers
{
	/**
	 * This will sort a collection based on the collection(array()) values
	 *
	 * @param array   $array  The array to sort
	 * @param mixed   $column The column to sort by
	 * @param integer $order  The order to sort the array (SORT_ASC, SORT_DESC)
	 */
	public static function array_qsort(&$array, $column = 0, $order = SORT_ASC)
	{
		$data = array();
		foreach($array as $key => $value)
		{
			$data[$key] = $value[$column];
		}
		
		array_multisort($data, $order, $array);
	}
	
	
	/**
	 * Get the size of a directory
	 *
	 * @param string $path The path to the directory
	 * @return array
	 */
	public static function getDirectorySize($path)
	{
		$size  = 0;
		$count = 0;
		$dirs  = 0;
		if(($dir = opendir($path)))
		{
			while(($file = readdir($dir)) !== false)
			{
				$path    .= '/' . $file;
				if($file != '.' && $file != '..' && !is_link($path))
				{
					if(is_dir($path))
					{
						$dirsize = self::getDirectorySize($path);
						$size   += $dirsize['size'];
						$count  += $dirsize['count'];
						$dirs   += $dirsize['dirs'] + 1;
					}
					else if(is_file($path))
					{
						$size   += filesize($path);
						$count++;
					}
				}
			}
			closedir($dir);
		}
		
		return array(
			'size'  => $size,
			'count' => $count,
			'dirs'  => $dirs,
		);
	}
	
	
	/**
	 * Parses an INI file with no interpretation of value content
	 * 
	 * @param  string $file The INI file to parse
	 * @author Jean-Jacques Guegan (http://mach13.com/loose-and-multiline-parse_ini_file-function-in-php)
	 */
	public static function parse_ini_file($file)
	{
		$matches =
		$result  = array();
		
		$a       = &$result;
		$s       = '\s*([[:alnum:]_\- \*]+?)\s*';
		
		preg_match_all('#^\s*((\[' . $s . '\])|(("?)' . $s . '\\5\s*=\s*("?)(.*?)\\7))\s*(;[^\n]*?)?$#ms', @file_get_contents($file), $matches, PREG_SET_ORDER);
		
		foreach($matches as $match)
		{
			if(empty($match[2]))
				$a[$match[6]] = $match[8];
			else
				$a            = &$result[$match[3]];
		}
		
		return $result;
	}
}