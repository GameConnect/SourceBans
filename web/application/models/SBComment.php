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
 * @property string $object_type Type
 * @property integer $object_id Object ID
 * @property integer $admin_id Admin ID
 * @property string $message Message
 * @property integer $update_admin_id Edited by
 * @property integer $update_time Edited on
 * @property integer $create_time Date/Time
 *
 * The followings are the available model relations:
 * @property SBAdmin $admin Admin
 * @property SBBan $ban Ban
 * @property SBProtest $protest Protest
 * @property SBSubmission $submission Submission
 * @property SBAdmin $update_admin Edit admin
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBComment extends CActiveRecord
{
	const BAN_TYPE        = 'B';
	const PROTEST_TYPE    = 'P';
	const SUBMISSION_TYPE = 'S';
	
	
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
			array('message', 'required'),
			array('message', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, object_type, object_id, admin_id, message, update_admin_id, update_time, create_time', 'safe', 'on'=>'search'),
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
			'ban' => array(self::BELONGS_TO, 'SBBan', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => self::BAN_TYPE)),
			'protest' => array(self::BELONGS_TO, 'SBProtest', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => self::PROTEST_TYPE)),
			'submission' => array(self::BELONGS_TO, 'SBSubmission', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => self::SUBMISSION_TYPE)),
			'update_admin' => array(self::BELONGS_TO, 'SBAdmin', 'update_admin_id'),
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
			'update_admin_id' => 'Edited by',
			'update_time' => 'Edited on',
			'create_time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
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
		$criteria->with='admin';

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.object_type',$this->object_type);
		$criteria->compare('t.object_id',$this->object_id);
		$criteria->compare('t.admin_id',$this->admin_id);
		$criteria->compare('t.message',$this->message,true);
		$criteria->compare('t.update_admin_id',$this->update_admin_id);
		$criteria->compare('t.update_time',$this->update_time);
		$criteria->compare('t.create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array(
					'create_time'=>CSort::SORT_DESC,
				),
			),
		));
	}

	public function behaviors()
	{
		return array(
			'CTimestampBehavior'=>array(
				'class'=>'zii.behaviors.CTimestampBehavior',
			),
		);
	}
}