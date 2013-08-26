<?php
class AdminTest extends CDbTestCase
{
	public function testCrud()
	{
		$model = new SBAdmin;
		$model->name = 'Test';
		$model->auth = SBAdmin::STEAM_AUTH;
		$model->identity = 'STEAM_0:1:2';
		$model->new_password = 'test';
		$this->assertTrue($model->save());
		
		$model = SBAdmin::model()->findByAttributes(array('name' => 'Test'));
		$group = SBGroup::model()->findByAttributes(array('name' => 'Owner'));
		$this->assertTrue($model !== null);
		$this->assertTrue($group !== null);
		
		$model->group_id = $group->id;
		$this->assertTrue($model->save());
		$this->assertTrue($model->delete());
	}
}