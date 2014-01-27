<?php
/**
 * Global data and functionality
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class Helpers
{
	/**
	 * Returns the values from a single column of the input array, identified by $column.
	 *
	 * @param array $array  The input array
	 * @param mixed $column The column of values to return
	 * @return array
	 */
	public static function array_column($array, $column = 0)
	{
		$data = array();
		foreach($array as $key => $value)
		{
			$data[$key] = $value[$column];
		}
		
		return $data;
	}
	
	
	/**
	 * This will sort a collection based on the collection(array()) values
	 *
	 * @param array   $array  The array to sort
	 * @param mixed   $column The column to sort by
	 * @param integer $order  The order to sort the array (SORT_ASC, SORT_DESC)
	 */
	public static function array_qsort(&$array, $column = 0, $order = SORT_ASC)
	{
		$data = self::array_column($array, $column);
		
		array_multisort($data, $order, $array);
	}
	
	
	/**
	 * Converts a Steam ID to a Community ID
	 *
	 * @param mixed $steam the Steam ID
	 * @return mixed the Community ID
	 */
	public static function getCommunityId($steam)
	{
		$params = $select = array();
		foreach((array)$steam as $i => $id)
		{
			$params[':id'.$i] = $id;
			$select[] = '76561197960265728 + CAST(MID(:id'.$i.', 9, 1) AS UNSIGNED) + CAST(MID(:id'.$i.', 11) * 2 AS UNSIGNED)';
		}
		
		$command = Yii::app()->db->createCommand();
		$command->select($select);
		$results = $command->queryRow(false, $params);
		
		return is_array($steam)
			? $results
			: $results[0];
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
	 * Converts a Community ID to a Steam ID
	 *
	 * @param mixed $community_id the Community ID
	 * @return mixed the Steam ID
	 */
	public static function getSteamId($community_id)
	{
		$params = $select = array();
		foreach((array)$community_id as $i => $id)
		{
			$params[':id'.$i] = $id;
			$select[] = 'CONCAT("STEAM_0:", (CAST(:id'.$i.' AS UNSIGNED) - 76561197960265728) % 2, ":", CAST(((CAST(:id'.$i.' AS UNSIGNED) - 76561197960265728) - ((CAST(:id'.$i.' AS UNSIGNED) - 76561197960265728) % 2)) / 2 AS UNSIGNED))';
		}
		
		$command = Yii::app()->db->createCommand();
		$command->select($select);
		$results = $command->queryRow(false, $params);
		
		return is_array($community_id)
			? $results
			: $results[0];
	}
	
	
	/**
	 * Parses the Community ID from a Steam ID, custom URL or profile URL
	 * 
	 * For example,
	 * <pre>
	 * Helpers::parseCommunityId('STEAM_0:1:16');
	 * Helpers::parseCommunityId('76561197960265761');
	 * Helpers::parseCommunityId('BAILOPAN');
	 * Helpers::parseCommunityId('http://steamcommunity.com/id/BAILOPAN');
	 * Helpers::parseCommunityId('http://steamcommunity.com/profiles/76561197960265761');
	 * </pre>
	 * 
	 * @param string $url the Steam ID, Custom URL or profile URL
	 * @return string the Community ID
	 */
	public static function parseCommunityId($url)
	{
		if(preg_match(SourceBans::PATTERN_STEAM, $url))
			return self::getCommunityId($url);
		
		if(preg_match('/steamcommunity\.com\/(id|profiles)\/([^\/?&])/i', $url, $matches))
		{
			$url     = $matches[2];
		}
		if(!is_numeric($url))
		{
			$profile = new SteamProfile($url);
			$url     = $profile->steamID64;
		}
		
		return $url;
	}
	
	
	/**
	 * Parses the Steam ID from a Community ID, custom URL or profile URL
	 * 
	 * For example,
	 * <pre>
	 * Helpers::parseSteamId('STEAM_0:1:16');
	 * Helpers::parseSteamId('76561197960265761');
	 * Helpers::parseSteamId('BAILOPAN');
	 * Helpers::parseSteamId('http://steamcommunity.com/id/BAILOPAN');
	 * Helpers::parseSteamId('http://steamcommunity.com/profiles/76561197960265761');
	 * </pre>
	 * 
	 * @param string $url the Community ID, custom URL or profile URL
	 * @return string the Steam ID
	 */
	public static function parseSteamId($url)
	{
		if(preg_match(SourceBans::PATTERN_STEAM, $url))
			return strtoupper($url);
		
		$url = self::parseCommunityId($url);
		return self::getSteamId($url);
	}
	
	
	/**
	 * Parses an INI file with no interpretation of value content
	 * 
	 * @param  string $file The INI file to parse
	 * @return array The parsed INI file
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