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
	
	
	public function runInstall()
	{
		// If Steam Web API Key is not set, disable installation
		return !empty(SourceBans::app()->settings->steam_web_api_key);
	}
	
	
	public function onBeginRequest($event)
	{
		// Register controller
		Yii::app()->controllerMap['communityBans'] = $this->getPathAlias('controllers.CommunityBansController');
	}
	
	public function onBeforeRender($view)
	{
		switch(Yii::app()->controller->route)
		{
			case 'admin/bans':
				if(!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission('ADD_BANS'))
				{
					// Add items to admin/bans menu
					Yii::app()->controller->menu[] = array(
						'divider' => true,
					);
					Yii::app()->controller->menu[] = array(
						'label' => Yii::t('CommunityBansPlugin.main', 'Ban Community friends'),
						'url' => array('communityBans/friends'),
					);
					Yii::app()->controller->menu[] = array(
						'label' => Yii::t('CommunityBansPlugin.main', 'Ban Community groups'),
						'url' => array('communityBans/groups'),
					);
				}
				break;
			case 'site/bans':
				if(!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission('ADD_BANS'))
				{
					// Add items to site/bans menu
					Yii::app()->controller->menu[] = array(
						'divider' => true,
					);
					Yii::app()->controller->menu[] = array(
						'label' => Yii::t('CommunityBansPlugin.main', 'Ban Community friends'),
						'url' => '#',
						'itemOptions' => array('class' => 'ban-menu-community-friends'),
					);
					Yii::app()->controller->menu[] = array(
						'label' => Yii::t('CommunityBansPlugin.main', 'Ban Community groups'),
						'url' => '#',
						'itemOptions' => array('class' => 'ban-menu-community-groups'),
					);
					
					// Register script
					Yii::app()->clientScript->registerScript('site_bans_communityBans', '
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