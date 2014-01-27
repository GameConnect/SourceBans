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
				$this->allowEmpty = ($object->type != SBBan::TYPE_IP);
				$this->pattern    = SourceBans::PATTERN_IP;
				break;
			case 'steam':
				$this->allowEmpty = ($object->type != SBBan::TYPE_STEAM);
				$this->pattern    = SourceBans::PATTERN_STEAM;
				break;
		}
		
		parent::clientValidateAttribute($object, $attribute);
	}
	
	
	protected function validateAttribute($object, $attribute)
	{
		switch($attribute)
		{
			case 'ip':
				$this->allowEmpty = ($object->type != SBBan::TYPE_IP);
				$this->pattern    = SourceBans::PATTERN_IP;
				break;
			case 'steam':
				$this->allowEmpty = ($object->type != SBBan::TYPE_STEAM);
				$this->pattern    = SourceBans::PATTERN_STEAM;
				break;
		}
		
		parent::validateAttribute($object, $attribute);
	}
}