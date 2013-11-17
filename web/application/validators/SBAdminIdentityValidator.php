<?php
/**
 * SourceBans admin identity validator
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SBAdminIdentityValidator extends CRegularExpressionValidator
{
	public function clientValidateAttribute($object, $attribute)
	{
		switch($object->auth)
		{
			case SBAdmin::STEAM_AUTH:
				$this->pattern = SourceBans::STEAM_PATTERN;
				break;
			case SBAdmin::IP_AUTH:
				$this->pattern = SourceBans::IP_PATTERN;
				break;
			default:
				return;
		}
		
		parent::clientValidateAttribute($object, $attribute);
	}
	
	
	protected function validateAttribute($object, $attribute)
	{
		switch($object->auth)
		{
			case SBAdmin::STEAM_AUTH:
				$this->pattern = SourceBans::STEAM_PATTERN;
				break;
			case SBAdmin::IP_AUTH:
				$this->pattern = SourceBans::IP_PATTERN;
				break;
			default:
				return;
		}
		
		parent::validateAttribute($object, $attribute);
	}
}