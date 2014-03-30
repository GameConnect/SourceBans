<?php
/**
 * Host validator
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class HostValidator extends CValidator
{
	const PATTERN_HOST = '/^([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])(\.([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9]))*$/';
	const PATTERN_IP   = '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
	
	
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty = true;
	
	/**
	 * @var boolean whether to invert the validation logic. Defaults to false. If set to true,
	 * the regular expression should NOT match the attribute value.
	 **/
	public $not = false;
	
	
	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object, $attribute)
	{
		$value = $object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		
		// reason of array checking explained here: https://github.com/yiisoft/yii/issues/1955
		if(is_array($value)
			|| (!$this->not && !$this->isValid($value))
			|| ($this->not && $this->isValid($value)))
		{
			$message = $this->message !== null ? $this->message : Yii::t('yii', '{attribute} is invalid.');
			$this->addError($object, $attribute, $message);
		}
	}
	
	/**
	 * Checks if the given value is valid.
	 * A value is considered valid if it is a hostname, or an IP address.
	 * @param string $value the value to be checked
	 * @return boolean whether the value is valid
	 */
	protected function isValid($value)
	{
		return preg_match(self::PATTERN_HOST, $value) || preg_match(self::PATTERN_IP, $value);
	}
}