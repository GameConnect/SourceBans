<?php
class UserIdBehavior extends CActiveRecordBehavior
{
	public $attributes = null;
	
	
	public function beforeSave($event)
	{
		if(!$this->owner->isNewRecord)
			return;
		
		if(Yii::app() instanceof CConsoleApplication || Yii::app()->user->isGuest)
			return;
		
		$id = Yii::app()->user->id;
		
		foreach((array)$this->attributes as $attribute)
		{
			$this->owner->{$attribute} = $id;
		}
	}
}