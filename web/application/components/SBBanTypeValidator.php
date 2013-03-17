<?php
class SBBanTypeValidator extends CRegularExpressionValidator
{
	public function clientValidateAttribute($object, $attribute)
	{
		switch($object->type)
		{
			case SBBan::STEAM_TYPE:
				if($attribute == 'steam')
				{
					$this->pattern = '/^STEAM_[0-9]:[0-9]:[0-9]+$/';
					$this->allowEmpty = false;
				}
				else
					$this->allowEmpty = true;
				break;
			case SBBan::IP_TYPE:
				if($attribute == 'ip')
				{
					$this->pattern = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
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
					$this->pattern = '/^STEAM_[0-9]:[0-9]:[0-9]+$/';
					$this->allowEmpty = false;
				}
				else
					$this->allowEmpty = true;
				break;
			case SBBan::IP_TYPE:
				if($attribute == 'ip')
				{
					$this->pattern = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
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