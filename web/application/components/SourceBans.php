<?php
/**
 * SourceBans global data and functionality
 *
 * @author    SteamFriends, InterWave Studios, GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link      http://www.sourcebans.net
 * @package   SourceBans
 * @version   $Id$
 */
class SourceBans extends CApplicationComponent
{
	private static $_app;
	
	
	// Block cloning and constructing
	private function __clone() {}
	private function __construct() {}
	
	
	/**
	 * @return stdClass a random SourceBans quote
	 */
	public function getQuote()
	{
		static $_quote;
		if(!isset($_quote))
		{
			$quotes = include Yii::getPathOfAlias('application.config') . '/quotes.php';
			$_quote = (object)$quotes[array_rand($quotes)];
		}
		
		return $_quote;
	}
	
	/**
	 * @return stdClass the SourceBans settings
	 */
	public function getSettings()
	{
		static $_settings;
		if(!isset($_settings))
		{
			$_settings = (object)CHtml::listData(SBSetting::model()->findAll(), 'name', 'value');
		}
		
		return $_settings;
	}
	
	/**
	 * @return string the version of SourceBans
	 */
	public function getVersion()
	{
		return '2.0.0-dev';
	}
	
	
	/**
	 * @return CApplicationComponent the component singleton
	 */
	public static function &app()
	{
		if(!self::$_app)
		{
			self::$_app = new self();
		}
	
		return self::$_app;
	}
}