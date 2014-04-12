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
		if(isset($this->_data[$name]))
			return $this->_data[$name];
		
		return parent::__get($name);
	}

	public function __set($name, $value)
	{
		if(isset($this->_data[$name]))
			return $this->_data[$name] = $value;
		
		parent::__set($name, $value);
	}


	public function init()
	{
		$this->_data = CHtml::listData(SBSetting::model()->findAll(), 'name', 'value');
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return array(
			array('default_page, items_per_page, language, password_min_length, theme, timezone', 'required'),
			array('dashboard_blocks_popup, bans_hide_admin, bans_hide_ip, bans_public_export, enable_appeals, enable_reports, enable_smtp', 'boolean'),
			array('items_per_page, password_min_length, smtp_port', 'numerical', 'integerOnly'=>true, 'min'=>1),
			array('mailer_from', 'email'),
			array('date_format, dashboard_text, dashboard_title, smtp_host, smtp_username, smtp_password, smtp_secure, steam_web_api_key', 'safe'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'dashboard_blocks_popup' => Yii::t('sourcebans', 'models.SettingsForm.dashboard_blocks_popup'),
			'dashboard_text' => '',
			'dashboard_title' => Yii::t('sourcebans', 'models.SettingsForm.dashboard_title'),
			'bans_hide_admin' => Yii::t('sourcebans', 'models.SettingsForm.bans_hide_admin'),
			'bans_hide_ip' => Yii::t('sourcebans', 'models.SettingsForm.bans_hide_ip'),
			'bans_public_export' => Yii::t('sourcebans', 'models.SettingsForm.bans_public_export'),
			'date_format' => Yii::t('sourcebans', 'models.SettingsForm.date_format'),
			'default_page' => Yii::t('sourcebans', 'models.SettingsForm.default_page'),
			'enable_appeals' => Yii::t('sourcebans', 'models.SettingsForm.enable_appeals'),
			'enable_smtp' => Yii::t('sourcebans', 'models.SettingsForm.enable_smtp'),
			'enable_reports' => Yii::t('sourcebans', 'models.SettingsForm.enable_reports'),
			'items_per_page' => Yii::t('sourcebans', 'models.SettingsForm.items_per_page'),
			'language' => Yii::t('sourcebans', 'models.SettingsForm.language'),
			'mailer_from' => Yii::t('sourcebans', 'models.SettingsForm.mailer_from'),
			'password_min_length' => Yii::t('sourcebans', 'models.SettingsForm.password_min_length'),
			'smtp_host' => Yii::t('sourcebans', 'models.SettingsForm.smtp_host'),
			'smtp_password' => Yii::t('sourcebans', 'models.SettingsForm.smtp_password'),
			'smtp_port' => Yii::t('sourcebans', 'models.SettingsForm.smtp_port'),
			'smtp_secure' => Yii::t('sourcebans', 'models.SettingsForm.smtp_secure'),
			'smtp_username' => Yii::t('sourcebans', 'models.SettingsForm.smtp_username'),
			'steam_web_api_key' => Yii::t('sourcebans', 'models.SettingsForm.steam_web_api_key'),
			'theme' => Yii::t('sourcebans', 'models.SettingsForm.theme'),
			'timezone' => Yii::t('sourcebans', 'models.SettingsForm.timezone'),
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
