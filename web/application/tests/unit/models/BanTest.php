<?php
class BanTest extends CDbTestCase
{
	public function testCrud()
	{
		$model = new SBBan;
		$model->name = 'Test';
		$model->type = SBBan::STEAM_TYPE;
		$model->steam = 'STEAM_0:1:2';
		$model->reason = 'Testing';
		$model->length = 5;
		$this->assertTrue($model->save());
		
		$model = SBBan::model()->findByPk($model->primaryKey);
		$this->assertTrue($model !== null);
		$this->assertTrue($model->isActive);
		$this->assertFalse($model->isPermanent);
		
		$model->ip = '1.2.3.4';
		$this->assertTrue($model->save());
		$this->assertTrue($model->delete());
	}
}