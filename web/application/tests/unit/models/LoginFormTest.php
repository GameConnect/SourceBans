<?php
class LoginFormTest extends DbTestCase
{
	public $fixtures = array(
		'admins' => 'SBAdmin',
	);
	
	protected function setUp()
	{
		// Expected session_regenerate_id() error
		$session = $this->getMock('CHttpSession', array('regenerateID'));
		
		$this->mockApplication(array(
			'components' => array(
				'session' => $session,
			),
		), 'CWebApplication');
		
		parent::setUp();
	}
	
	public function testValidate()
	{
		$model = new LoginForm;
		$model->username = 'Demo';
		$model->password = 'demo';
		$this->assertTrue($model->validate());
	}
	
	public function testLogin()
	{
		$model = new LoginForm;
		$model->username = 'Demo';
		$model->password = 'demo';
		$this->assertTrue($model->login());
		$this->assertFalse(Yii::app()->user->isGuest);
	}
}