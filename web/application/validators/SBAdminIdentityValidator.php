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
			case SBAdmin::AUTH_STEAM:
				$this->pattern = SourceBans::PATTERN_STEAM;
				break;
			case SBAdmin::AUTH_IP:
				$this->pattern = SourceBans::PATTERN_IP;
				break;
			default:
				return;
		}
		
		return parent::clientValidateAttribute($object, $attribute);
	}
	
	
	protected function validateAttribute($object, $attribute)
	{
		switch($object->auth)
		{
			case SBAdmin::AUTH_STEAM:
				$this->pattern = SourceBans::PATTERN_STEAM;
				break;
			case SBAdmin::AUTH_IP:
				$this->pattern = SourceBans::PATTERN_IP;
				break;
			default:
				return;
		}
		
		parent::validateAttribute($object, $attribute);
	}
}