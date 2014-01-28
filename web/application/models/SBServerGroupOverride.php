<?php

/**
 * This is the model class for table "{{server_group_overrides}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{server_group_overrides}}':
 * @property integer $group_id Group ID
 * @property string $type Type
 * @property string $name Name
 * @property string $access Access
 *
 * The followings are the available model relations:
 * @property SBServerGroup $group Group
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBServerGroupOverride extends CActiveRecord
{
	const ACCESS_ALLOW = 'allow';
	const ACCESS_DENY  = 'deny';
	const TYPE_COMMAND = 'command';
	const TYPE_GROUP   = 'group';
	
	
	public function __toString()
	{
		return $this->name;
	}
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBServerGroupOverride the static model class
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
		return '{{server_group_overrides}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('group_id, type, name, access', 'required'),
			array('group_id', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>7),
			array('name', 'length', 'max'=>32),
			array('access', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('group_id, type, name, access', 'safe', 'on'=>'search'),
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
			'group' => array(self::BELONGS_TO, 'SBServerGroup', 'group_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'group_id' => Yii::t('sourcebans', 'Server group'),
			'type' => Yii::t('sourcebans', 'Type'),
			'name' => Yii::t('sourcebans', 'Name'),
			'access' => Yii::t('sourcebans', 'Access'),
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

		$criteria->compare('t.group_id', $this->group_id);
		$criteria->compare('t.type', $this->type, true);
		$criteria->compare('t.name', $this->name, true);
		$criteria->compare('t.access', $this->access, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	
	/**
	 * Returns the supported access types
	 * 
	 * @return array the supported access types
	 */
	public static function getAccessTypes()
	{
		return array(
			self::ACCESS_ALLOW => Yii::t('sourcebans', 'Allow'),
			self::ACCESS_DENY  => Yii::t('sourcebans', 'Deny'),
		);
	}
	
	/**
	 * Returns the supported override types
	 * 
	 * @return array the supported override types
	 */
	public static function getTypes()
	{
		return array(
			self::TYPE_COMMAND => Yii::t('sourcebans', 'Command'),
			self::TYPE_GROUP   => Yii::t('sourcebans', 'Group'),
		);
	}
}