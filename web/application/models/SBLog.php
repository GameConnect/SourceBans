<?php

/**
 * This is the model class for table "{{logs}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{logs}}':
 * @property integer $id ID
 * @property string $type Type
 * @property string $title Title
 * @property string $message Message
 * @property string $function Function
 * @property string $query Query
 * @property integer $admin_id Admin ID
 * @property string $admin_ip Admin IP address
 * @property integer $time Date/Time
 *
 * The followings are the available model relations:
 * @property SBAdmin $admin
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBLog extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBLog the static model class
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
		return '{{logs}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, title, message, function, query, admin_id, admin_ip', 'required'),
			array('admin_id', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>1),
			array('title', 'length', 'max'=>64),
			array('message, function, query', 'length', 'max'=>255),
			array('admin_ip', 'match', 'pattern'=>'^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, title, message, function, query, admin_id, admin_ip, time', 'safe', 'on'=>'search'),
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
			'title' => Yii::t('sourcebans', 'Title'),
			'message' => Yii::t('sourcebans', 'Message'),
			'function' => Yii::t('sourcebans', 'Function'),
			'query' => Yii::t('sourcebans', 'Query'),
			'admin_id' => Yii::t('sourcebans', 'Admin'),
			'admin_ip' => 'Admin IP address',
			'time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
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

		$criteria->compare('id',$this->id);
		$criteria->compare('type',$this->type);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('message',$this->message,true);
		$criteria->compare('function',$this->function,true);
		$criteria->compare('query',$this->query,true);
		$criteria->compare('admin_id',$this->admin_id);
		$criteria->compare('admin_ip',$this->admin_ip,true);
		$criteria->compare('time',$this->time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
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
				'updateAttribute' => null,
			),
		);
	}
}