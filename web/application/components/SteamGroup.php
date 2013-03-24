<?php
require_once 'SteamCommunity.php';

/**
 * Steam Community group
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @property string $groupID64 Steam Community ID
 * @property string $groupName Steam Community name
 * @property string $groupURL URL
 * @property string $headline Headline
 * @property string $summary Summary
 * @property string $avatarIcon Icon-sized avatar
 * @property string $avatarMedium Medium-sized avatar
 * @property string $avatarFull Full-sized avatar
 * @property integer $memberCount Member count
 * @property integer $membersInChat Members in chat
 * @property integer $membersInGame Members in game
 * @property integer $membersOnline Members online
 * @property integer $totalPages Total pages
 * @property integer $currentPage Current page
 * @property integer $startingMember Starting member
 * @property array $members Members
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SteamGroup
{
	/**
	 * @var array The group data
	 */
	private $_data;
	
	/**
	 * @var string The Steam Community ID or custom URL
	 */
	private $_id;
	
	
	/**
	 * Constructor
	 * 
	 * @param string $id The Steam Community ID or custom URL
	 */
	function __construct($id)
	{
		$this->_id = $id;
	}
	
	/**
	 * Returns a property value
	 *
	 * @param string $name The property name
	 * @return mixed The property value
	 */
	public function __get($name)
	{
		if(empty($this->_data))
		{
			$this->_requestData();
		}
		
		if(isset($this->_data[$name]))
			return $this->_data[$name];
		
		return null;
	}
	
	
	/**
	 * Requests the group data
	 */
	private function _requestData()
	{
		$data = $this->_request('memberslistxml');
		if(empty($data))
			return;
		
		$data = new SimpleXMLElement($data);
		
		$this->_data = array(
			'groupID64' => (string)$data->groupID64,
			'groupName' => (string)$data->groupDetails->groupName,
			'groupURL' => (string)$data->groupDetails->groupURL,
			'headline' => (string)$data->groupDetails->headline,
			'summary' => (string)$data->groupDetails->summary,
			'avatarIcon' => (string)$data->groupDetails->avatarIcon,
			'avatarMedium' => (string)$data->groupDetails->avatarMedium,
			'avatarFull' => (string)$data->groupDetails->avatarFull,
			'memberCount' => (int)$data->groupDetails->memberCount,
			'membersInChat' => (int)$data->groupDetails->membersInChat,
			'membersInGame' => (int)$data->groupDetails->membersInGame,
			'membersOnline' => (int)$data->groupDetails->membersOnline,
			'totalPages' => (int)$data->totalPages,
			'currentPage' => (int)$data->currentPage,
			'startingMember' => (int)$data->startingMember,
		);
		
		if(isset($data->members))
		{
			$this->_data['members'] = array();
			foreach($data->members->steamID64 as $steamID64)
			{
				$this->_data['members'][] = (string)$steamID64;
			}
		}
	}
	
	/**
	 * Normalizes a request
	 * 
	 * @param string $path
	 * @param array $data
	 * @return string
	 */
	private function _request($path, $data = array())
	{
		$path = (is_numeric($this->_id) ? 'gid/' . $this->_id : 'groups/' . $this->_id) . '/' . $path;
		
		return SteamCommunity::request($path, $data);
	}
}