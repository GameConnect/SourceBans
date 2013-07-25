<?php
class CommunityBansController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}
	
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'users'=>array('@'),
				'expression'=>'Yii::app()->user->data->hasPermission("ADD_BANS")',
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	
	public function actionFriends()
	{
		$this->pageTitle=Yii::t('CommunityBansPlugin.main', 'Ban Community Friends');
		
		$this->breadcrumbs=array(
			$this->pageTitle,
		);
		
		$plugin = SBPlugin::model()->findById('CommunityBans');
		$id     = Yii::app()->request->getQuery('id');
		
		if(Yii::app()->request->isAjaxRequest && !empty($id))
		{
			$profile  = new SteamProfile($id);
			$steamids = Helpers::array_column($profile->getFriends(), 'steamid');
			$friends  = SteamProfile::getSummaries($steamids);
		}
		if(!isset($friends))
			$friends = array();
		
		$dataProvider = new CArrayDataProvider($friends, array(
			'keyField' => 'steamid',
			'pagination' => false,
			'sort' => array(
				'attributes' => array(
					'personaname',
					'*',
				),
				'defaultOrder' => array(
					'personaname' => CSort::SORT_ASC,
				),
			),
		));
		
		$assetsUrl = Yii::app()->assetManager->publish($plugin->getPath('assets'));
		Yii::app()->clientScript->registerCssFile($assetsUrl . '/css/community-bans.css');
		
		$this->render($plugin->getViewFile('friends'), array(
			'plugin' => $plugin,
			'dataProvider' => $dataProvider,
		));
	}
	
	public function actionGroups()
	{
		$this->pageTitle=Yii::t('CommunityBansPlugin.main', 'Ban Community Groups');
		
		$this->breadcrumbs=array(
			$this->pageTitle,
		);
		
		$plugin = SBPlugin::model()->findById('CommunityBans');
		$id     = Yii::app()->request->getQuery('id');
		
		if(Yii::app()->request->isAjaxRequest && !empty($id))
		{
			$profile = new SteamProfile($id);
			$groups  = $profile->groups;
		}
		if(!isset($groups))
			$groups = array();
		
		$dataProvider = new CArrayDataProvider($groups, array(
			'keyField' => 'groupID64',
			'pagination' => false,
			'sort' => array(
				'attributes' => array(
					'groupName',
					'memberCount',
					'*',
				),
				'defaultOrder' => array(
					'groupName' => CSort::SORT_ASC,
				),
			),
		));
		
		$assetsUrl = Yii::app()->assetManager->publish($plugin->getPath('assets'));
		Yii::app()->clientScript->registerCssFile($assetsUrl . '/css/community-bans.css');
		
		$this->render($plugin->getViewFile('groups'), array(
			'plugin' => $plugin,
			'dataProvider' => $dataProvider,
		));
	}
	
	public function actionBanFriends($id)
	{
		$profile = new SteamProfile($id);
		$friends = Yii::app()->request->getPost('steamids');
		
		do
		{
			// SteamProfile::getSummaries is limited to 100 Steam IDs,
			// so repeatedly remove the first 100 until the array is empty.
			$steamids = array_splice($friends, 0, 100);
			$profiles = SteamProfile::getSummaries($steamids);
			
			$steamids = Helpers::getSteamId($steamids);
			foreach($profiles as $i => $friend)
			{
				$ban         = new SBBan;
				$ban->type   = SBBan::STEAM_TYPE;
				$ban->steam  = $steamids[$i];
				$ban->name   = $friend['personaname'];
				$ban->reason = 'Friend of Steam Community profile "' . $profile->steamID . '"';
				$ban->length = 0;
				$ban->save();
			}
		}
		while(!empty($friends));
	}
	
	public function actionBanGroup($id)
	{
		$group   = new SteamGroup($id);
		$members = $group->members;
		
		do
		{
			// SteamProfile::getSummaries is limited to 100 Steam IDs,
			// so repeatedly remove the first 100 until the array is empty.
			$steamids = array_splice($members, 0, 100);
			$profiles = SteamProfile::getSummaries($steamids);
			
			$steamids = Helpers::getSteamId($steamids);
			foreach($profiles as $i => $member)
			{
				$ban         = new SBBan;
				$ban->type   = SBBan::STEAM_TYPE;
				$ban->steam  = $steamids[$i];
				$ban->name   = $member['personaname'];
				$ban->reason = 'Member of Steam Community group "' . $group->groupName . '"';
				$ban->length = 0;
				$ban->save();
			}
		}
		while(!empty($members));
	}
}