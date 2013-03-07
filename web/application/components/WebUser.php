<?php
/**
 * @property SBAdmin $data SourceBans admin data
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