<?php
class ServerTest extends CDbTestCase
{
	public function testCrud()
	{
		$game = SBGame::model()->findByAttributes(array('folder' => 'tf'));
		$this->assertTrue($game !== null);
		
		$model = new SBServer;
		$model->ip = '1.2.3.4';
		$model->game_id = $game->id;
		$this->assertTrue($model->save());
		
		$model = SBServer::model()->findByAttributes(array('ip' => '1.2.3.4'));
		$this->assertTrue($model !== null);
		
		$model->port = 27016;
		$model->enabled = false;
		$this->assertTrue($model->save());
		$this->assertTrue($model->delete());
	}
}