<?php
class UserIpBehavior extends CActiveRecordBehavior
{
	public $attributes = null;
	
	
	public function beforeSave($event)
	{
		if(!$this->owner->isNewRecord)
			return;
		
		$ip = Yii::app() instanceof CConsoleApplication
			? '127.0.0.1'
			: Yii::app()->request->userHostAddress;
		
		foreach((array)$this->attributes as $attribute)
		{
			$this->owner->{$attribute} = $ip;
		}
	}
}