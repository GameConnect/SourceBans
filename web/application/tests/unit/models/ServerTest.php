<?php
class ServerTest extends DbTestCase
{
	public $fixtures = array(
		'games'   => 'SBGame',
		'servers' => 'SBServer',
	);
	
	public function testCreate()
	{
		$game  = $this->games('tf');
		$model = new SBServer;
		
		$model->ip      = '1.2.3.4';
		$model->game_id = $game->id;
		$this->assertTrue($model->save());
	}
	
	public function testUpdate()
	{
		$model = $this->servers('192.168.1.2');
		
		$model->port    = 27017;
		$model->enabled = true;
		$this->assertTrue($model->save());
	}
	
	public function testDelete()
	{
		$model = $this->servers('192.168.1.2');
		$this->assertTrue($model->delete());
	}
}