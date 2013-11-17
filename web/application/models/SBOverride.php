<?php

/**
 * This is the model class for table "{{overrides}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{overrides}}':
 * @property string $type Type
 * @property string $name Name
 * @property string $flags Flags
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBOverride extends CActiveRecord
{
	const COMMAND_TYPE = 'command';
	const GROUP_TYPE   = 'group';
	
	
  public function __toString()
	{
		return $this->name;
	}
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBOverride the static model class
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
		return '{{overrides}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, name, flags', 'required'),
			array('type', 'length', 'max'=>7),
			array('name', 'length', 'max'=>32),
			array('flags', 'length', 'max'=>30),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('type, name, flags', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'type' => Yii::t('sourcebans', 'Type'),
			'name' => Yii::t('sourcebans', 'Name'),
			'flags' => Yii::t('sourcebans', 'Server permissions'),
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

		$criteria->compare('t.type',$this->type,true);
		$criteria->compare('t.name',$this->name,true);
		$criteria->compare('t.flags',$this->flags,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
			'sort'=>array(
				'defaultOrder'=>array(
					'type'=>CSort::SORT_ASC,
					'name'=>CSort::SORT_ASC,
				),
			),
		));
	}
	
	
	/**
	 * Returns the supported override types
	 * 
	 * @return array the supported override types
	 */
	public static function getTypes()
	{
		return array(
			self::COMMAND_TYPE => Yii::t('sourcebans', 'Command'),
			self::GROUP_TYPE   => Yii::t('sourcebans', 'Group'),
		);
	}
}