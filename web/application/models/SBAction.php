<?php

/**
 * This is the model class for table "{{actions}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{actions}}':
 * @property integer $id ID
 * @property string $name Name
 * @property string $steam Steam ID
 * @property string $ip IP address
 * @property string $message Message
 * @property integer $server_id Server ID
 * @property integer $admin_id Admin ID
 * @property string $admin_ip Admin IP address
 * @property integer $create_time Date/Time
 *
 * The followings are the available model relations:
 * @property SBAdmin $admin Admin
 * @property SBServer $server Server
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBAction extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBAction the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{actions}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, steam, ip, message, server_id, admin_id, admin_ip, create_time', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'admin' => array(self::BELONGS_TO, 'SBAdmin', 'admin_id'),
			'server' => array(self::BELONGS_TO, 'SBServer', 'server_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => Yii::t('sourcebans', 'Name'),
			'steam' => Yii::t('sourcebans', 'Steam ID'),
			'ip' => Yii::t('sourcebans', 'IP address'),
			'message' => Yii::t('sourcebans', 'Message'),
			'server_id' => Yii::t('sourcebans', 'Server'),
			'admin_id' => Yii::t('sourcebans', 'Admin'),
			'admin_ip' => 'Admin IP address',
			'create_time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
			'admin.name' => Yii::t('sourcebans', 'Admin'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;
		$criteria->with=array('admin','server');

		$criteria->compare('t.id', $this->id);
		$criteria->compare('t.name', $this->name, true);
		$criteria->compare('t.steam', $this->steam, true);
		$criteria->compare('t.ip', $this->ip, true);
		$criteria->compare('t.message', $this->message, true);
		$criteria->compare('t.server_id', $this->server_id);
		$criteria->compare('t.admin_id', $this->admin_id);
		$criteria->compare('t.admin_ip', $this->admin_ip, true);
		$criteria->compare('t.create_time', $this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
			'sort'=>array(
				'attributes'=>array(
					'admin.name'=>array(
						'asc'=>'admin.name',
						'desc'=>'admin.name DESC',
					),
					'*',
				),
				'defaultOrder'=>array(
					'create_time'=>CSort::SORT_DESC,
				),
			),
		));
	}
}