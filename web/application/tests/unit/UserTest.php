<?php
class UserTest extends CDbTestCase
{
	public function testLogin()
	{
		// Expected session_regenerate_id() error
		$this->setExpectedException('PHPUnit_Framework_Error_Warning');
		
		$model = new LoginForm;
		$model->username = 'Demo';
		$model->password = 'demo';
		$this->assertTrue($model->validate());
		$this->assertTrue($model->login());
		
		$this->assertFalse(Yii::app()->user->isGuest);
		$this->assertType('SBAdmin', Yii::app()->user->data);
		
		Yii::app()->user->logout();
		$this->assertTrue(Yii::app()->user->isGuest);
	}
}