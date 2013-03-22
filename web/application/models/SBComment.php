<?php

/**
 * This is the model class for table "{{comments}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{comments}}':
 * @property integer $id ID
 * @property string $type Type
 * @property integer $ban_id Ban ID
 * @property integer $admin_id Admin ID
 * @property string $message Message
 * @property integer $time Date/Time
 * @property integer $edit_admin_id Edited by
 * @property integer $edit_time Edited on
 *
 * The followings are the available model relations:
 * @property SBBan $ban
 * @property SBAdmin $admin
 * @property SBAdmin $edit_admin
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBComment extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBComment the static model class
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
		return '{{comments}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, ban_id, admin_id, message, time', 'required'),
			array('ban_id, admin_id, edit_admin_id', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>1),
			array('message', 'length', 'max'=>255),
			array('time, edit_time', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, ban_id, admin_id, message, time, edit_admin_id, edit_time', 'safe', 'on'=>'search'),
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
			'ban' => array(self::BELONGS_TO, 'SBBan', 'ban_id'),
			'admin' => array(self::BELONGS_TO, 'SBAdmin', 'admin_id'),
			'edit_admin' => array(self::BELONGS_TO, 'SBAdmin', 'edit_admin_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'type' => Yii::t('sourcebans', 'Type'),
			'ban_id' => Yii::t('sourcebans', 'Ban'),
			'admin_id' => Yii::t('sourcebans', 'Admin'),
			'message' => Yii::t('sourcebans', 'Message'),
			'time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
			'edit_admin_id' => 'Edited by',
			'edit_time' => 'Edited on',
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

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.type',$this->type,true);
		$criteria->compare('t.ban_id',$this->ban_id);
		$criteria->compare('t.admin_id',$this->admin_id);
		$criteria->compare('t.message',$this->message,true);
		$criteria->compare('t.time',$this->time,true);
		$criteria->compare('t.edit_admin_id',$this->edit_admin_id);
		$criteria->compare('t.edit_time',$this->edit_time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array(
					'time'=>CSort::SORT_DESC,
				),
			),
		));
	}

	public function behaviors()
	{
		return array(
			'CTimestampBehavior' => array(
				'class' => 'zii.behaviors.CTimestampBehavior',
				'createAttribute' => 'time',
				'updateAttribute' => 'edit_time',
			),
		);
	}
}