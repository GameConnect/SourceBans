<?php
Yii::import('system.test.CTestCase');

class TestCase extends CTestCase
{
	/**
	 * Clean up after test.
	 * By default the application created with {@link mockApplication()} will be destroyed.
	 */
	protected function tearDown()
	{
		parent::tearDown();
		$this->destroyApplication();
	}
	
	/**
	 * Populates Yii::app() with a new application
	 * The application will be destroyed on {@link tearDown()} automatically.
	 * @param array  $config   The application configuration, if needed
	 * @param string $appClass name of the application class to create
	 */
	protected function mockApplication($config = array(), $appClass = 'CConsoleApplication')
	{
		$defaultConfig = require __DIR__ . '/../config/test.php';
		
		new $appClass(CMap::mergeArray($defaultConfig, $config));
	}
	
	/**
	 * Destroys application in Yii::app() by setting it to null.
	 */
	protected function destroyApplication()
	{
		Yii::setApplication(null);
	}
}
