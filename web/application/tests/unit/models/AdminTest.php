<?php
class AdminTest extends DbTestCase
{
	public $fixtures = array(
		'admins' => 'SBAdmin',
		'groups' => 'SBGroup',
	);
	
	public function testCreate()
	{
		$model = new SBAdmin;
		
		$model->name     = 'Local';
		$model->auth     = SBAdmin::AUTH_IP;
		$model->identity = '127.0.0.1';
		$model->setPassword('localhost');
		$this->assertTrue($model->save());
	}
	
	public function testUpdate()
	{
		$model = $this->admins('Test');
		$group = $this->groups('Root');
		
		$model->group_id = $group->id;
		$this->assertTrue($model->save());
	}
	
	public function testDelete()
	{
		$model = $this->admins('Test');
		$this->assertTrue($model->delete());
	}
}