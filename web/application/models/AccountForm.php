<?php

/**
 * AccountForm is the data structure for the user data.
 * It is used by the 'account' action of 'SiteController'.
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * @package sourcebans.models
 * @since 2.0
 */
class AccountForm extends CFormModel
{
	public $language;
	public $theme;
	public $timezone;
	public $new_email;
	public $confirm_email;
	public $current_password;
	public $new_password;
	public $confirm_password;
	public $new_server_password;
	public $confirm_server_password;


	public function __get($name)
	{
		if(Yii::app()->user->data->hasAttribute($name))
			return Yii::app()->user->data->$name;
		
		return parent::__get($name);
	}


	public function init()
	{
		foreach($this->attributes as $name => $value)
		{
			if(Yii::app()->user->data->hasAttribute($name))
				$this->$name = Yii::app()->user->data->$name;
		}
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('language, theme, timezone', 'default', 'setOnEmpty'=>true, 'on'=>'settings'),
			array('new_email, confirm_email', 'required', 'on'=>'email'),
			array('new_email, confirm_email', 'email', 'on'=>'email'),
			array('confirm_email', 'compare', 'compareAttribute'=>'new_email', 'message'=>Yii::t('yii', '{attribute} must be repeated exactly.', array('{attribute}'=>'{compareAttribute}')), 'on'=>'email'),
			array('current_password, new_password, confirm_password', 'required', 'on'=>'password'),
			array('current_password, new_password, confirm_password', 'length', 'min'=>SourceBans::app()->settings->password_min_length, 'on'=>'password'),
			array('current_password', 'validateCurrentPassword', 'message'=>Yii::t('yii', '{attribute} is invalid.'), 'on'=>'password'),
			array('confirm_password', 'compare', 'compareAttribute'=>'new_password', 'message'=>Yii::t('yii', '{attribute} must be repeated exactly.', array('{attribute}'=>'{compareAttribute}')), 'on'=>'email'),
			array('new_server_password, confirm_server_password', 'required', 'on'=>'server_password'),
			array('new_server_password, confirm_server_password', 'length', 'min'=>SourceBans::app()->settings->password_min_length, 'on'=>'server_password'),
			array('confirm_server_password', 'compare', 'compareAttribute'=>'new_server_password', 'message'=>Yii::t('yii', '{attribute} must be repeated exactly.', array('{attribute}'=>'{compareAttribute}')), 'on'=>'email'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'language' => Yii::t('sourcebans', 'models.AccountForm.language'),
			'theme' => Yii::t('sourcebans', 'models.AccountForm.theme'),
			'timezone' => Yii::t('sourcebans', 'models.AccountForm.timezone'),
			'email' => Yii::t('sourcebans', 'models.AccountForm.email'),
			'new_email' => Yii::t('sourcebans', 'models.AccountForm.new_email'),
			'confirm_email' => Yii::t('sourcebans', 'models.AccountForm.confirm_email'),
			'current_password' => Yii::t('sourcebans', 'models.AccountForm.current_password'),
			'new_password' => Yii::t('sourcebans', 'models.AccountForm.new_password'),
			'confirm_password' => Yii::t('sourcebans', 'models.AccountForm.confirm_password'),
			'new_server_password' => Yii::t('sourcebans', 'models.AccountForm.new_server_password'),
			'confirm_server_password' => Yii::t('sourcebans', 'models.AccountForm.confirm_server_password'),
		);
	}

	/**
	 * Saves the user data using the given values in the model.
	 * @return boolean whether save is successful
	 */
	public function save()
	{
		switch($this->scenario)
		{
			case 'email':
				Yii::app()->user->data->email = $this->new_email;
				break;
			case 'password':
				Yii::app()->user->data->setPassword($this->new_password);
				break;
			case 'server_password':
				Yii::app()->user->data->server_password = $this->new_server_password;
				break;
			case 'settings':
				foreach($this->attributes as $name => $value)
				{
					if(Yii::app()->user->data->hasAttribute($name))
						Yii::app()->user->data->$name = $value;
				}
				break;
			default:
				return false;
		}
		
		Yii::app()->user->data->save();
		return true;
	}

	public function validateCurrentPassword()
	{
		return Yii::app()->user->data->validatePassword($this->current_password);
	}
}
