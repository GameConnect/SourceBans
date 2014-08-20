<?php
class CommunityBansPlugin extends SBPlugin
{
	public function getName()
	{
		return 'Steam Community Bans';
	}
	
	public function getDescription()
	{
		return Yii::t('CommunityBansPlugin.main', 'Ban Steam Community friends and groups');
	}
	
	public function getAuthor()
	{
		return 'GameConnect';
	}
	
	public function getVersion()
	{
		return '1.0';
	}
	
	public function getUrl()
	{
		return 'http://www.gameconnect.net';
	}
	
	
	public function init()
	{
		SourceBans::app()->on('app.beginRequest', array($this, 'onBeginRequest'));
		SourceBans::app()->on('app.beforeRender', array($this, 'onBeforeRender'));
	}
	
	public function runInstall()
	{
		// If Steam Web API Key is not set, disable installation
		if(empty(SourceBans::app()->settings->steam_web_api_key))
			throw new CException(Yii::t('sourcebans', 'controllers.plugins.install.errors.steam_web_api_key'));
	}
	
	
	public function onBeginRequest($event)
	{
		// Register controller
		Yii::app()->controllerMap['communityBans'] = $this->getPathAlias('controllers.CommunityBansController');
		
		// Add permissions
		SourceBans::app()->permissions->add('BAN_COMMUNITY_FRIENDS', Yii::t('CommunityBansPlugin.main', 'Ban Community friends'));
		SourceBans::app()->permissions->add('BAN_COMMUNITY_GROUPS',  Yii::t('CommunityBansPlugin.main', 'Ban Community groups'));
	}
	
	public function onBeforeRender($event)
	{
		switch(Yii::app()->controller->route)
		{
			case 'admin/bans':
				if(!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission('BAN_COMMUNITY_FRIENDS', 'BAN_COMMUNITY_GROUPS'))
				{
					// Add items to admin/bans menu
					Yii::app()->controller->menu[] = array(
						'divider' => true,
					);
					Yii::app()->controller->menu[] = array(
						'label' => Yii::t('CommunityBansPlugin.main', 'Ban Community friends'),
						'url' => array('communityBans/friends'),
						'visible' => 'Yii::app()->user->data->hasPermission("BAN_COMMUNITY_FRIENDS")',
					);
					Yii::app()->controller->menu[] = array(
						'label' => Yii::t('CommunityBansPlugin.main', 'Ban Community groups'),
						'url' => array('communityBans/groups'),
						'visible' => 'Yii::app()->user->data->hasPermission("BAN_COMMUNITY_GROUPS")',
					);
				}
				break;
			case 'default/bans':
				if(!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission('BAN_COMMUNITY_FRIENDS', 'BAN_COMMUNITY_GROUPS'))
				{
					// Add items to default/bans menu
					Yii::app()->controller->menu[] = array(
						'divider' => true,
					);
					Yii::app()->controller->menu[] = array(
						'label' => Yii::t('CommunityBansPlugin.main', 'Ban Community friends'),
						'url' => '#',
						'itemOptions' => array('class' => 'ban-menu-community-friends'),
						'visible' => 'Yii::app()->user->data->hasPermission("BAN_COMMUNITY_FRIENDS")',
					);
					Yii::app()->controller->menu[] = array(
						'label' => Yii::t('CommunityBansPlugin.main', 'Ban Community groups'),
						'url' => '#',
						'itemOptions' => array('class' => 'ban-menu-community-groups'),
						'visible' => 'Yii::app()->user->data->hasPermission("BAN_COMMUNITY_GROUPS")',
					);
					
					// Register script
					Yii::app()->clientScript->registerScript('default_bans_communityBans', '
  $(document).on("click", "li.ban-menu-community-friends > a", function() {
    viewCommunityBans(this, "' . Yii::app()->createUrl('communityBans/friends', array('id' => '__ID__')) . '");
  });
  $(document).on("click", "li.ban-menu-community-groups > a", function() {
    viewCommunityBans(this, "' . Yii::app()->createUrl('communityBans/groups', array('id' => '__ID__')) . '");
  });
  function viewCommunityBans(el, url) {
    var $el         = $(el),
        $header     = $el.parents("tr.section").prev("tr.header"),
        communityId = $header.data("communityId");
    
    if(communityId) {
      location.href = url.replace("__ID__", communityId);
    }
    else {
      $el.parents("li").addClass("disabled");
    }
  }
', CClientScript::POS_READY);
				}
				break;
		}
	}
}