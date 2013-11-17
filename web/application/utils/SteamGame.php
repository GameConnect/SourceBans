<?php
require_once 'SteamCommunity.php';

/**
 * Steam game
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SteamGame
{
	/**
	 * @var integer The game application ID
	 */
	private $_id;
	
	
	/**
	 * Constructor
	 * 
	 * @param integer $id The game application ID
	 */
	function __construct($id)
	{
		$this->_id = $id;
	}
	
	
	/**
	 * ISteamNews/GetNewsForApp/v0002
	 * 
	 * @param integer $count How many news enties you want to get returned
	 * @param integer $maxlength Maximum length of each news entry
	 * @return array An array of news item information
	 */
	public function getNews($count = 3, $maxlength = 300)
	{
		$data = SteamCommunity::apiRequest('ISteamNews', 'GetNewsForApp', 2, array(
			'appid' => $this->_id,
			'count' => $count,
			'maxlength' => $maxlength,
		));
		if(empty($data))
			return array();
		
		$data = json_decode($data, true);
		return $data['appnews']['newsitems'];
	}
	
	/**
	 * ISteamUserStats/GetSchemaForGame/v0002
	 * 
	 * @param string $lang Localized language to return 
	 * @return array An array of game schema information
	 */
	public function getSchema($lang = 'en')
	{
		$data = SteamCommunity::apiRequest('ISteamUserStats', 'GetSchemaForGame', 2, array(
			'appid' => $this->_id,
			'l' => $lang,
		));
		if(empty($data))
			return array();
		
		$data = json_decode($data, true);
		return $data['game'];
	}
}