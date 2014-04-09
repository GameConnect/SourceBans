<?php
/**
 * Locale data
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class LocaleData
{
	public static function getCountries()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = include Yii::getPathOfAlias('application.data') . '/countries.php';
		}
		
		return $_data;
	}
	
	
	public static function getCountry($id)
	{
		$countries = self::getCountries();
		
		return $countries[$id];
	}
	
	
	public static function getLanguage($id)
	{
		$languages = self::getLanguages();
		
		return $languages[$id];
	}
	
	
	public static function getLanguages()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = include Yii::getPathOfAlias('application.data') . '/languages.php';
		}
		
		return $_data;
	}
	
	
	public static function getTimezone($id)
	{
		$timezones = self::getTimezones();
		
		return $timezones[$id];
	}
	
	
	public static function getTimezones()
	{
		static $_data;
		if(!isset($_data))
		{
			$_data = include Yii::getPathOfAlias('application.data') . '/timezones.php';
		}
		
		return $_data;
	}
}