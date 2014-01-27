<?php
require_once 'SteamCommunity.php';

/**
 * Steam Community profile
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @property string $steamID64 Steam Community ID
 * @property string $steamID Steam Community name
 * @property string $onlineState Online state
 * @property string $stateMessage State message
 * @property integer $visibilityState Visibility state
 * @property string $privacyState Privacy state
 * @property string $avatarIcon Icon-sized avatar
 * @property string $avatarMedium Medium-sized avatar
 * @property string $avatarFull Full-sized avatar
 * @property integer $vacBanned Whether the account is VAC banned
 * @property string $tradeBanState Trade ban state
 * @property integer $isLimitedAccount Whether the account is limited
 * @property string $customURL Custom URL
 * @property string $memberSince Member since
 * @property float $steamRating Steam rating
 * @property float $hoursPlayed2Wk Hours played past 2 weeks
 * @property string $headline Headline
 * @property string $location Location
 * @property string $realname Real name
 * @property string $summary Summary
 * @property array $inGameInfo In-game info
 * @property array $mostPlayedGames Most played games
 * @property array $webLinks Web links
 * @property array $groups Groups
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SteamProfile
{
	const VISIBILITY_STATE_HIDDEN  = 1;
	const VISIBILITY_STATE_VISIBLE = 3;
	
	/**
	 * @var array The profile data
	 */
	private $_data;
	
	/**
	 * @var string The Steam Community ID or custom URL
	 */
	private $_id;
	
	
	/**
	 * Constructor
	 * 
	 * @param string $id The Steam Community ID, custom URL or profile URL
	 */
	public function __construct($id)
	{
		if(preg_match('/steamcommunity\.com\/(id|profiles)\/([^\/?&])/i', $id, $matches))
			$this->_id = $matches[2];
		else
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
	 * ISteamUser/GetFriendList/v0001
	 * 
	 * @return array An array of friends
	 */
	public function getFriends()
	{
		$data = SteamCommunity::apiRequest('ISteamUser', 'GetFriendList', 1, array(
			'relationship' => 'friend',
			'steamid' => is_numeric($this->_id) ? $this->_id : $this->steamID64,
		));
		if(empty($data))
			return array();
		
		$data = json_decode($data, true);
		return $data['friendslist']['friends'];
	}
	
	/**
	 * Returns the owned games
	 * 
	 * @return array An array of owned games
	 */
	public function getGames()
	{
		$data = $this->_request('games');
		if(empty($data))
			return array();
		
		$data = new SimpleXMLElement($data);
		
		$this->_data['steamID64'] = (string)$data->steamID64;
		$this->_data['steamID'] = (string)$data->steamID;
		
		$results = array();
		foreach($data->games->game as $game)
		{
			$results[] = (object)array(
				'appID' => (string)$game->appID,
				'name' => (string)$game->name,
				'logo' => (string)$game->logo,
				'storeLink' => (string)$game->storeLink,
				'hoursLast2Weeks' => (float)$game->hoursLast2Weeks,
				'hoursOnRecord' => (int)$game->hoursOnRecord,
				'statsLink' => (string)$game->statsLink,
				'globalStatsLink' => (string)$game->globalStatsLink,
			);
		}
		
		return $results;
	}
	
	
	/**
	 * ISteamUser/GetPlayerSummaries/v0002
	 * 
	 * @param array $steamids List of 64 bit Steam IDs to return profile information for. Up to 100 Steam IDs can be requested.
	 * @return array An array of player summaries
	 */
	public static function getSummaries(array $steamids)
	{
		$data = SteamCommunity::apiRequest('ISteamUser', 'GetPlayerSummaries', 2, array(
			'steamids' => implode(',', $steamids),
		));
		if(empty($data))
			return array();
		
		$data = json_decode($data, true);
		return $data['response']['players'];
	}
	
	
	/**
	 * Requests the profile data
	 */
	private function _requestData()
	{
		$data = $this->_request();
		if(empty($data))
			return;
		
		$data = new SimpleXMLElement($data);
		
		$this->_data = array(
			'steamID64' => (string)$data->steamID64,
			'steamID' => (string)$data->steamID,
			'onlineState' => (string)$data->onlineState,
			'stateMessage' => (string)$data->stateMessage,
			'privacyState' => (string)$data->privacyState,
			'visibilityState' => (int)$data->visibilityState,
			'avatarIcon' => (string)$data->avatarIcon,
			'avatarMedium' => (string)$data->avatarMedium,
			'avatarFull' => (string)$data->avatarFull,
			'vacBanned' => (int)$data->vacBanned,
			'tradeBanState' => (string)$data->tradeBanState,
			'isLimitedAccount' => (int)$data->isLimitedAccount,
			'mostPlayedGames' => array(),
			'webLinks' => array(),
			'groups' => array(),
		);
		
		if($this->privacyState == 'public')
		{
			$this->_data['customURL'] = (string)$data->customURL;
			$this->_data['memberSince'] = (string)$data->memberSince;
			$this->_data['steamRating'] = (float)$data->steamRating;
			$this->_data['hoursPlayed2Wk'] = (float)$data->hoursPlayed2Wk;
			$this->_data['headline'] = (string)$data->headline;
			$this->_data['location'] = (string)$data->location;
			$this->_data['realname'] = (string)$data->realname;
			$this->_data['summary'] = (string)$data->summary;
		}
		if($this->onlineState == 'in-game')
		{
			$this->_data['inGameInfo'] = (object)array(
				'gameName' => (string)$data->inGameInfo->gameName,
				'gameLink' => (string)$data->inGameInfo->gameLink,
				'gameIcon' => (string)$data->inGameInfo->gameIcon,
				'gameLogo' => (string)$data->inGameInfo->gameLogo,
				'gameLogoSmall' => (string)$data->inGameInfo->gameLogoSmall,
			);
		}
		
		if(isset($data->mostPlayedGames))
		{
			foreach($data->mostPlayedGames->mostPlayedGame as $mostPlayedGame)
			{
				$this->_data['mostPlayedGames'][] = (object)array(
					'gameName' => (string)$mostPlayedGame->gameName,
					'gameLink' => (string)$mostPlayedGame->gameLink,
					'gameIcon' => (string)$mostPlayedGame->gameIcon,
					'gameLogo' => (string)$mostPlayedGame->gameLogo,
					'gameLogoSmall' => (string)$mostPlayedGame->gameLogoSmall,
					'hoursPlayed' => (float)$mostPlayedGame->hoursPlayed,
					'hoursOnRecord' => (int)$mostPlayedGame->hoursOnRecord,
					'statsName' => (string)$mostPlayedGame->statsName,
				);
			}
		}
		if(isset($data->webLinks))
		{
			foreach($data->webLinks->webLink as $webLink)
			{
				$this->_data['webLinks'][] = (object)array(
					'title' => (string)$webLink->title,
					'link' => (string)$webLink->link,
				);
			}
		}
		if(isset($data->groups))
		{
			foreach($data->groups->group as $group)
			{
				$this->_data['groups'][] = (object)array(
					'groupID64' => (string)$group->groupID64,
					'groupName' => (string)$group->groupName,
					'groupURL' => (string)$group->groupURL,
					'headline' => (string)$group->headline,
					'summary' => (string)$group->summary,
					'avatarIcon' => (string)$group->avatarIcon,
					'avatarMedium' => (string)$group->avatarMedium,
					'avatarFull' => (string)$group->avatarFull,
					'memberCount' => (int)$group->memberCount,
					'membersInChat' => (int)$group->membersInChat,
					'membersInGame' => (int)$group->membersInGame,
					'membersOnline' => (int)$group->membersOnline,
					'isPrimary' => (int)$group['isPrimary'],
				);
			}
		}
	}
	
	/**
	 * Normalizes a request
	 * 
	 * @param string $path The request path
	 * @param array $data The request data
	 * @return string The request output
	 */
	private function _request($path = '', $data = array())
	{
		$path = (is_numeric($this->_id) ? 'profiles/' . $this->_id : 'id/' . $this->_id)
		      . (!empty($path) ? '/' . $path : '');
		
		return SteamCommunity::request($path, $data);
	}
}