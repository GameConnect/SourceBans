<?php
/**
 * SourceBans ban type validator
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SBBanTypeValidator extends CRegularExpressionValidator
{
	public function clientValidateAttribute($object, $attribute)
	{
		switch($attribute)
		{
			case 'ip':
				$this->allowEmpty = ($object->type != SBBan::IP_TYPE);
				$this->pattern    = SourceBans::IP_PATTERN;
				break;
			case 'steam':
				$this->allowEmpty = ($object->type != SBBan::STEAM_TYPE);
				$this->pattern    = SourceBans::STEAM_PATTERN;
				break;
		}
		
		parent::clientValidateAttribute($object, $attribute);
	}
	
	
	protected function validateAttribute($object, $attribute)
	{
		switch($attribute)
		{
			case 'ip':
				$this->allowEmpty = ($object->type != SBBan::IP_TYPE);
				$this->pattern    = SourceBans::IP_PATTERN;
				break;
			case 'steam':
				$this->allowEmpty = ($object->type != SBBan::STEAM_TYPE);
				$this->pattern    = SourceBans::STEAM_PATTERN;
				break;
		}
		
		parent::validateAttribute($object, $attribute);
	}
}