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
		switch($object->type)
		{
			case SBBan::STEAM_TYPE:
				if($attribute == 'steam')
				{
					$this->pattern = SourceBans::STEAM_PATTERN;
					$this->allowEmpty = false;
				}
				else
					$this->allowEmpty = true;
				break;
			case SBBan::IP_TYPE:
				if($attribute == 'ip')
				{
					$this->pattern = SourceBans::IP_PATTERN;
					$this->allowEmpty = false;
				}
				else
					$this->allowEmpty = true;
				break;
			default:
				return;
		}
		
		parent::clientValidateAttribute($object, $attribute);
	}
	
	
	protected function validateAttribute($object, $attribute)
	{
		switch($object->type)
		{
			case SBBan::STEAM_TYPE:
				if($attribute == 'steam')
				{
					$this->pattern = SourceBans::STEAM_PATTERN;
					$this->allowEmpty = false;
				}
				else
					$this->allowEmpty = true;
				break;
			case SBBan::IP_TYPE:
				if($attribute == 'ip')
				{
					$this->pattern = SourceBans::IP_PATTERN;
					$this->allowEmpty = false;
				}
				else
					$this->allowEmpty = true;
				break;
			default:
				return;
		}
		
		parent::validateAttribute($object, $attribute);
	}
}