<?php
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

/**
 * SourceBans global data and functionality
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @property CMap $flags The supported SourceMod flags
 * @property array $languages The supported SourceBans languages
 * @property CMap $permissions The supported SourceBans permissions
 * @property array $plugins The enabled SourceBans plugins
 * @property object $quote A random SourceBans quote
 * @property CAttributeCollection $settings The SourceBans settings
 * @property array $themes The installed SourceBans themes
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SourceBans extends CApplicationComponent
{
	const PATTERN_HOST   = '/^([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])(\.([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9]))*$/';
	const PATTERN_IP     = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
	const PATTERN_STATUS = '/# +([0-9]+) +"(.+)" +(STEAM_[0-9]:[0-9]:[0-9]+) +([0-9:]+) +([0-9]+) +([0-9]+) +([a-zA-Z]+) +([0-9.:]+)/';
	const PATTERN_STEAM  = '/^STEAM_[0-9]:[0-9]:[0-9]+$/i';
	
	/**
	 * @var array the attached event handlers (event name => handlers)
	 */
	private $_events;
	
	private static $_app;
	
	
	// Block cloning and constructing
	private function __clone() {}
	private function __construct() {}
	
	
	/**
	 * Returns the supported SourceMod flags
	 * 
	 * @return CMap the supported SourceMod flags
	 */
	public function getFlags()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = new CMap(include Yii::getPathOfAlias('application.data') . '/flags.php', true);
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
			asort($_data);
		}
		
		return $_data;
	}
	
	/**
	 * Returns the supported SourceBans permissions
	 * 
	 * @return CMap the supported SourceBans permissions
	 */
	public function getPermissions()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = new CMap(include Yii::getPathOfAlias('application.data') . '/permissions.php');
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
	 * @return CAttributeCollection the SourceBans settings
	 */
	public function getSettings()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = new CAttributeCollection(CHtml::listData(SBSetting::model()->findAll(), 'name', 'value'), true);
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
			asort($_data);
		}
		
		return $_data;
	}
	
	/**
	 * Attaches an event handler to an event.
	 * 
	 * An event handler must be a valid PHP callback. The followings are
	 * some examples:
	 * <pre>
	 * function($event) { ... }         // PHP 5.3: anonymous function
	 * array($object, 'handleClick')    // $object->handleClick()
	 * array('Page', 'handleClick')     // Page::handleClick()
	 * 'handleClick'                    // global function handleClick()
	 * </pre>
	 * An event handler must be defined with the following signature,
	 * <pre>
	 * function($event)
	 * </pre>
	 * where $event is a {@link CEvent} object which includes parameters associated with the event.
	 * 
	 * @param string $name the event name
	 * @param callback $handler the event handler
	 * @see off()
	 */
	public function on($name, $handler)
	{
		$this->_events[$name][] = $handler;
	}
	
	/**
	 * Detaches an existing event handler from this component.
	 * This method is the opposite of {@link on()}.
	 * 
	 * @param string $name event name
	 * @param callback $handler the event handler to be removed.
	 * If it is null, all handlers attached to the named event will be removed.
	 * @return boolean if a handler is found and detached
	 * @see on()
	 */
	public function off($name, $handler = null)
	{
		if(!isset($this->_events[$name]))
			return false;
		
		if($handler === null)
		{
			$this->_events[$name] = array();
			return false;
		}
		
		$removed = false;
		foreach($this->_events[$name] as $i => $event)
		{
			if($event !== $handler)
				continue;
			
			unset($this->_events[$name][$i]);
			$removed = true;
		}
		if($removed)
			$this->_events[$name] = array_values($this->_events[$name]);
		
		return $removed;
	}
	
	/**
	 * Triggers an event.
	 * This method represents the happening of an event. It invokes
	 * all attached handlers for the event.
	 * 
	 * @param string $name the event name
	 * @param CEvent $event the event parameter. If not set, a default {@link CEvent} object will be created.
	 */
	public function trigger($name, $event = null)
	{
		if(empty($this->_events[$name]))
			return;
		
		if($event === null)
			$event = new CEvent;
		if($event->sender === null)
			$event->sender = $this;
		
		$event->handled = false;
		foreach($this->_events[$name] as $handler)
		{
			call_user_func($handler, $event);
			// stop further handling if the event is handled
			if($event instanceof CEvent && $event->handled)
				return;
		}
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
	 * @param string $title title of the message
	 * @param string $message message to be logged
	 * @param string $type type of the message ({@link SBLog}::TYPE_ERROR, {@link SBLog}::TYPE_INFORMATION, {@link SBLog}::TYPE_WARNING).
	 */
	public static function log($title, $message, $type = SBLog::TYPE_INFORMATION)
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
	 * @throws CHttpException if the /install folder exists
	 */
	public static function onBeginRequest($event)
	{
		if(!YII_DEBUG && file_exists(Yii::getPathOfAlias('webroot.install')))
			throw new CHttpException(403, 'Please delete the /install folder.');
		
		// Set timezone
		if(!Yii::app()->user->isGuest && !empty(Yii::app()->user->data->timezone))
			Yii::app()->setTimeZone(Yii::app()->user->data->timezone);
		else if(!empty(SourceBans::app()->settings->timezone))
			Yii::app()->setTimeZone(SourceBans::app()->settings->timezone);
		
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
		if(!empty(SourceBans::app()->settings->mailer_from))
			Yii::app()->mailer->From     = SourceBans::app()->settings->mailer_from;
		
		SteamCommunity::setApiKey(SourceBans::app()->settings->steam_web_api_key);
		
		SourceBans::app()->getPlugins();
		SourceBans::app()->trigger('app.beginRequest', $event);
	}
	
	/**
	 * Raised right AFTER the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	public static function onEndRequest($event)
	{
		SourceBans::app()->trigger('app.endRequest', $event);
	}
}