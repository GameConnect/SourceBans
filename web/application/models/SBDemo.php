<?php

/**
 * This is the model class for table "{{demos}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{demos}}':
 * @property integer $id ID
 * @property integer $object_id Object ID
 * @property string $object_type Type
 * @property string $filename Filename
 *
 * The followings are the available model relations:
 * @property SBBan $ban
 * @property SBSubmission $submission
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBDemo extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBDemo the static model class
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
		return '{{demos}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('object_id, object_type, filename', 'required'),
			array('object_id', 'numerical', 'integerOnly'=>true),
			array('object_type', 'length', 'max'=>1),
			array('filename', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, object_id, object_type, filename', 'safe', 'on'=>'search'),
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
			'ban' => array(self::BELONGS_TO, 'SBBan', 'object_id', 'condition' => 'object_type = "B"'),
			'submission' => array(self::BELONGS_TO, 'SBSubmission', 'object_id', 'condition' => 'object_type = "S"'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'object_id' => Yii::t('sourcebans', 'Object'),
			'object_type' => Yii::t('sourcebans', 'Type'),
			'filename' => Yii::t('sourcebans', 'Filename'),
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('object_id',$this->object_id);
		$criteria->compare('object_type',$this->object_type,true);
		$criteria->compare('filename',$this->filename,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}