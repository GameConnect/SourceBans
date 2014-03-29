<?php
class BanTest extends DbTestCase
{
	public $fixtures = array(
		'admins'  => 'SBAdmin',
		'bans'    => 'SBBan',
		'servers' => 'SBServer',
	);
	
	public function testIsActive()
	{
		$model = $this->bans('Hacker');
		$this->assertTrue($model->isActive);
	}
	
	public function testIsNotPermanent()
	{
		$model = $this->bans('Newbie');
		$this->assertFalse($model->isPermanent);
	}
	
	public function testCreate()
	{
		$admin  = $this->admins('Test');
		$server = $this->servers('127.0.0.1');
		$model  = new SBBan;
		
		$model->name      = 'Tester';
		$model->type      = SBBan::TYPE_STEAM;
		$model->steam     = 'STEAM_0:1:2';
		$model->reason    = 'Testing';
		$model->length    = 5;
		$model->server_id = $server->id;
		$model->admin_id  = $admin->id;
		$this->assertTrue($model->save());
	}
	
	public function testUpdate()
	{
		$model = $this->bans('Newbie');
		
		$model->ip = '1.2.3.4';
		$this->assertTrue($model->save());
	}
	
	public function testDelete()
	{
		$model = $this->bans('Newbie');
		$this->assertTrue($model->delete());
	}
}