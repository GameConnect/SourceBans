<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 * 
 * @property integer $id SourceBans admin ID
 */
class UserIdentity extends CUserIdentity
{
	protected $_id;


	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$admin = SBAdmin::model()->find(array(
			'condition' => 'name = :username OR email = :username',
			'params' => array(':username' => $this->username),
		));
		
		if($admin === null)
			$this->errorCode = self::ERROR_USERNAME_INVALID;
		else if(!$admin->validatePassword($this->password))
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
		else
		{
			$this->errorCode = self::ERROR_NONE;
			
			$this->_id       = $admin->id;
			$this->username  = $admin->name;
		}
		return !$this->errorCode;
	}

	public function getId()
	{
		return $this->_id;
	}
}