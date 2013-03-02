<?php
/**
 * SourceBans global data and functionality
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @property array $permissions The supported SourceBans permissions
 * @property array $plugins The enabled SourceBans plugins
 * @property object $quote A random SourceBans quote
 * @property object $settings The SourceBans settings
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SourceBans extends CApplicationComponent
{
	private static $_app;
	
	
	// Block cloning and constructing
	private function __clone() {}
	private function __construct() {}
	
	
	/**
	 * Returns the supported SourceBans permissions
	 * 
	 * @return array the supported SourceBans permissions
	 */
	public function getPermissions()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = include Yii::getPathOfAlias('application.data') . '/permissions.php';
		}
		
		return $_data;
	}
	
	/**
	 * Returns the enabled SourceBans plugins
	 * 
	 * @return array the enabled SourceBans plugins
	 */
	public function getPlugins()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = SBPlugin::model()->enabled()->findAll();
		}
		
		return $_data;
	}
	
	/**
	 * Returns a random SourceBans quote
	 * 
	 * @return object a random SourceBans quote
	 */
	public function getQuote()
	{
		static $_data;
		if(!isset($_data))
		{
			$quotes = include Yii::getPathOfAlias('application.data') . '/quotes.php';
			$_data  = (object)$quotes[array_rand($quotes)];
		}
		
		return $_data;
	}
	
	/**
	 * Returns the SourceBans settings
	 * 
	 * @return object the SourceBans settings
	 */
	public function getSettings()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = (object)CHtml::listData(SBSetting::model()->findAll(), 'name', 'value');
		}
		
		return $_data;
	}
	
	
	/**
	 * Returns the SourceBans component singleton
	 * 
	 * @return SourceBans the SourceBans component singleton
	 */
	public static function &app()
	{
		if(!self::$_app)
		{
			self::$_app = new self();
		}
		
		return self::$_app;
	}
	
	/**
	 * Returns the version of SourceBans
	 * 
	 * @return string the version of SourceBans
	 */
	public static function getVersion()
	{
		return '2.0.0-dev';
	}
	
	/**
	 * Raised right BEFORE the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	public static function onBeginRequest($event)
	{
		// Set timezone
		// TODO: Make a timezone list based on names instead of hour offsets. Etc/GMT may be deprecated in the future.
		$timezone = SourceBans::app()->settings->timezone + SourceBans::app()->settings->summer_time;
		date_default_timezone_set('Etc/GMT' . ($timezone < 0 ? $timezone : '+' . $timezone));
		
		// Set date/time format
		Yii::app()->format->datetimeFormat = SourceBans::app()->settings->date_format ?: 'm-d-y H:i';
		
		// Set language
		if(!Yii::app()->user->isGuest && !empty(Yii::app()->user->data->language))
			Yii::app()->setLanguage(Yii::app()->user->data->language);
		else
			Yii::app()->setLanguage(SourceBans::app()->settings->language);
		
		// Set theme
		if(!Yii::app()->user->isGuest && !empty(Yii::app()->user->data->theme))
			Yii::app()->setTheme(Yii::app()->user->data->theme);
		else
			Yii::app()->setTheme(SourceBans::app()->settings->theme);
		
		// Call onBeginRequest on SourceBans plugins
		foreach(SourceBans::app()->plugins as $plugin)
			$plugin->onBeginRequest($event);
	}
	
	/**
	 * Raised right AFTER the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	public static function onEndRequest($event)
	{
		// Call onEndRequest on SourceBans plugins
		foreach(SourceBans::app()->plugins as $plugin)
			$plugin->onEndRequest($event);
	}
}