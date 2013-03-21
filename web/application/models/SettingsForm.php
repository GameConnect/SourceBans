<?php

/**
 * SettingsForm is the data structure for the application settings.
 * It is used by the 'settings' action of 'AdminController'.
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SettingsForm extends CFormModel
{
	private $_data = array();


	public function __get($name)
	{
		if(property_exists(SourceBans::app()->settings, $name))
			return SourceBans::app()->settings->$name;
		
		return parent::__get($name);
	}

	public function __set($name, $value)
	{
		if(property_exists(SourceBans::app()->settings, $name))
			return $this->_data[$name] = $value;
		
		parent::__set($name, $value);
	}
	

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('default_page, items_per_page, language, password_min_length, theme, timezone', 'required'),
			array('bans_hide_admin, bans_hide_ip, bans_public_export, disable_log_popup, enable_protest, enable_smtp, enable_submit', 'boolean'),
			array('items_per_page, password_min_length, smtp_port', 'numerical', 'integerOnly'=>true, 'min'=>1),
			array('dashboard_text, dashboard_title, smtp_host, smtp_username, smtp_password, smtp_secure', 'safe'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'dashboard_text' => '',
			'dashboard_title' => Yii::t('sourcebans', 'Title'),
			'bans_hide_admin' => Yii::t('sourcebans', 'Hide admins'),
			'bans_hide_ip' => Yii::t('sourcebans', 'Hide IP addresses'),
			'bans_public_export' => Yii::t('sourcebans', 'Enable public export'),
			'date_format' => Yii::t('sourcebans', 'Date format'),
			'default_page' => Yii::t('sourcebans', 'Default page'),
			'enable_protest' => Yii::t('sourcebans', 'Enable Protest ban'),
			'enable_smtp' => Yii::t('sourcebans', 'Enable SMTP'),
			'enable_submit' => Yii::t('sourcebans', 'Enable Submit ban'),
			'items_per_page' => Yii::t('sourcebans', 'Items per page'),
			'language' => Yii::t('sourcebans', 'Language'),
			'password_min_length' => Yii::t('sourcebans', 'Min password length'),
			'smtp_host' => Yii::t('sourcebans', 'SMTP host'),
			'smtp_password' => Yii::t('sourcebans', 'SMTP password'),
			'smtp_port' => Yii::t('sourcebans', 'SMTP port'),
			'smtp_secure' => Yii::t('sourcebans', 'SMTP security'),
			'smtp_username' => Yii::t('sourcebans', 'SMTP username'),
			'theme' => Yii::t('sourcebans', 'Theme'),
			'timezone' => Yii::t('sourcebans', 'Timezone'),
		);
	}

	/**
	 * Saves the application settings using the given values in the model.
	 * @return boolean whether save is successful
	 */
	public function save()
	{
		$settings = SBSetting::model()->findAll(array('index' => 'name'));
		
		foreach($this->_data as $name => $value)
		{
			$settings[$name]->value = trim($value);
			$settings[$name]->save();
		}
		
		return true;
	}
}
