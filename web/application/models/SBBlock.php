<?php

/**
 * This is the model class for table "{{blocks}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{blocks}}':
 * @property integer $ban_id Ban ID
 * @property string $name Name
 * @property integer $server_id Server ID
 * @property integer $create_time Date/Time
 *
 * The followings are the available model relations:
 * @property SBBan $ban Ban
 * @property SBServer $server Server
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBBlock extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBBlock the static model class
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
		return '{{blocks}}';
	}

	public function primaryKey()
	{
		return array('ban_id', 'name', 'server_id', 'create_time');
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
			array('ban_id, name, server_id, create_time', 'safe', 'on'=>'search'),
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
			'server' => array(self::BELONGS_TO, 'SBServer', 'server_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'ban_id' => Yii::t('sourcebans', 'Ban'),
			'name' => Yii::t('sourcebans', 'Name'),
			'server_id' => Yii::t('sourcebans', 'Server'),
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
		$criteria->with=array('ban','server');

		$criteria->compare('t.ban_id',$this->ban_id);
		$criteria->compare('t.name',$this->name,true);
		$criteria->compare('t.server_id',$this->server_id);
		$criteria->compare('t.create_time',$this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
			'sort'=>array(
				'defaultOrder'=>array(
					'create_time'=>CSort::SORT_DESC,
				),
			),
		));
	}
}