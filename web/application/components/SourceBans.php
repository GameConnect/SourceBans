<?php
/**
 * SourceBans global data and functionality
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @property array $flags The supported SourceMod flags
 * @property array $languages The supported SourceBans languages
 * @property array $permissions The supported SourceBans permissions
 * @property array $plugins The enabled SourceBans plugins
 * @property object $quote A random SourceBans quote
 * @property object $settings The SourceBans settings
 * @property array $themes The installed SourceBans themes
 * 
 * @package sourcebans.components
 * @since 2.0
 */
define('SM_RESERVATION', 'a');
define('SM_GENERIC',     'b');
define('SM_KICK',        'c');
define('SM_BAN',         'd');
define('SM_UNBAN',       'e');
define('SM_SLAY',        'f');
define('SM_CHANGEMAP',   'g');
define('SM_CONVARS',     'h');
define('SM_CONFIG',      'i');
define('SM_CHAT',        'j');
define('SM_VOTE',        'k');
define('SM_PASSWORD',    'l');
define('SM_RCON',        'm');
define('SM_CHEATS',      'n');
define('SM_CUSTOM1',     'o');
define('SM_CUSTOM2',     'p');
define('SM_CUSTOM3',     'q');
define('SM_CUSTOM4',     'r');
define('SM_CUSTOM5',     's');
define('SM_CUSTOM6',     't');
define('SM_ROOT',        'z');

class SourceBans extends CApplicationComponent
{
	const IP_PATTERN     = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
	const STATUS_PATTERN = '/#[ ]*([0-9]+) "(.+)" (STEAM_[0-9]:[0-9]:[0-9]+)[ ]{1,2}([0-9]+[:[0-9]+) ([0-9]+)[ ]([0-9]+) ([a-zA-Z]+) ([0-9.:]+)/';
	const STEAM_PATTERN  = '/^STEAM_[0-9]:[0-9]:[0-9]+$/i';
	
	private static $_app;
	
	
	// Block cloning and constructing
	private function __clone() {}
	private function __construct() {}
	
	
	/**
	 * Returns the supported SourceMod flags
	 * 
	 * @return array the supported SourceMod flags
	 */
	public function getFlags()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = include Yii::getPathOfAlias('application.data') . '/flags.php';
		}
		
		return $_data;
	}
	
	/**
	 * Returns the supported SourceBans languages
	 * 
	 * @return array the supported SourceBans languages
	 */
	public function getLanguages()
	{
		static $_data = array();
		if(empty($_data))
		{
			$basePath = Yii::app()->getMessages()->basePath;
			$folder = @opendir($basePath);
			while(($file = @readdir($folder)) !== false)
			{
				if($file{0} === '.' || !is_dir($basePath . DIRECTORY_SEPARATOR .  $file))
				  continue;
				
				$_data[$file] = CLocale::getInstance($file)->getLocaleDisplayName($file);
				if($file !== Yii::app()->language)
					$_data[$file] .= ' (' . Yii::app()->locale->getLocaleDisplayName($file) . ')';
			}
			closedir($folder);
			ksort($_data);
		}
		
		return $_data;
	}
	
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
			$_data = SBPlugin::model()->enabled()->findAll(array('index' => 'id'));
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
	 * Returns the installed SourceBans themes
	 * 
	 * @return array the installed SourceBans themes
	 */
	public function getThemes()
	{
		static $_data = array();
		if(empty($_data))
		{
			$themeNames = Yii::app()->getThemeManager()->getThemeNames();
			foreach($themeNames as $themeName)
			{
				$_data[$themeName] = ucfirst($themeName);
			}
			sort($_data);
		}
		
		return $_data;
	}
	
	
	/**
	 * Returns the SourceBans application singleton
	 * 
	 * @return SourceBans the SourceBans application singleton
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
	 * Logs a message.
	 * @param string $message message to be logged
	 * @param string $title title of the message
	 * @param string $type type of the message ({@link SBLog}::ERROR_TYPE, {@link SBLog}::INFORMATION_TYPE, {@link SBLog}::WARNING_TYPE).
	 */
	public static function log($message, $title, $type = SBLog::INFORMATION_TYPE)
	{
		$log          = new SBLog;
		$log->type    = $type;
		$log->title   = $title;
		$log->message = $message;
		$log->save();
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
		if(!Yii::app()->user->isGuest && !empty(Yii::app()->user->data->timezone))
			date_default_timezone_set(Yii::app()->user->data->timezone);
		else if(!empty(SourceBans::app()->settings->timezone))
			date_default_timezone_set(SourceBans::app()->settings->timezone);
		else
			date_default_timezone_set('Europe/London');
		
		// Set date/time format
		if(!empty(SourceBans::app()->settings->date_format))
			Yii::app()->format->datetimeFormat = SourceBans::app()->settings->date_format;
		
		// Set language
		if(!Yii::app()->user->isGuest && !empty(Yii::app()->user->data->language))
			Yii::app()->setLanguage(Yii::app()->user->data->language);
		else if(!empty(SourceBans::app()->settings->language))
			Yii::app()->setLanguage(SourceBans::app()->settings->language);
		
		// Set theme
		if(!Yii::app()->user->isGuest && !empty(Yii::app()->user->data->theme))
			Yii::app()->setTheme(Yii::app()->user->data->theme);
		else if(!empty(SourceBans::app()->settings->theme))
			Yii::app()->setTheme(SourceBans::app()->settings->theme);
		
		// Set mailer
		if(SourceBans::app()->settings->enable_smtp)
		{
			Yii::app()->mailer->mailer   = 'smtp';
			Yii::app()->mailer->host     = SourceBans::app()->settings->smtp_host;
			Yii::app()->mailer->port     = SourceBans::app()->settings->smtp_port;
			Yii::app()->mailer->username = SourceBans::app()->settings->smtp_username;
			Yii::app()->mailer->password = SourceBans::app()->settings->smtp_password;
			Yii::app()->mailer->security = SourceBans::app()->settings->smtp_secure;
		}
		
		SteamCommunity::setApiKey(SourceBans::app()->settings->steam_web_api_key);
		
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