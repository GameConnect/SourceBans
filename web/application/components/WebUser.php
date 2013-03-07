<?php
/**
 * WebUser represents the persistent state for a Web application user.
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @property SBAdmin $data SourceBans admin data
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class WebUser extends CWebUser
{
	public function getData()
	{
		static $_data;
		if(!isset($_data) && !$this->isGuest)
		{
			$_data = SBAdmin::model()->findByPk($this->id);
		}
		
		return $_data;
	}
	
	
	protected function afterLogin($fromCookie)
	{
		$this->data->lastvisit = time();
		$this->data->save();
	}
}