<?php

/**
 * LostPasswordForm is the data structure for resetting a user password.
 * It is used by the 'lostPassword' action of 'SiteController'.
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * @package sourcebans.models
 * @since 2.0
 */
class LostPasswordForm extends CFormModel
{
	public $email;

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('email', 'required'),
			array('email', 'email'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'email' => Yii::t('sourcebans', 'models.LostPasswordForm.email'),
		);
	}

	/**
	 * Resets the user password using the given email in the model.
	 * @return boolean whether reset is successful
	 */
	public function reset()
	{
		/** @var SBAdmin $admin */
		$admin = SBAdmin::model()->findByAttributes(array(
			'email' => $this->email,
		));
		if($admin === null)
			return false;
		
		$validationKey = Yii::app()->securityManager->generateRandomString(32, false);
		Yii::app()->mailer->AddAddress($admin->email);
		Yii::app()->mailer->Subject = Yii::t('sourcebans', 'models.LostPasswordForm.reset.subject');
		Yii::app()->mailer->MsgHtml(Yii::t('sourcebans', 'models.LostPasswordForm.reset.body', array(
			'{name}' => $admin->name,
			'{link}' => Yii::app()->createUrl('site/lostPassword', array('email' => $admin->email, 'key' => $validationKey)),
		)));
		if(!Yii::app()->mailer->Send())
			return false;
		
		$admin->validation_key = $validationKey;
		$admin->save(false);
		return true;
	}
}
