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
 * @property string $object_type Type
 * @property integer $object_id Object ID
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
	const BAN_TYPE        = 'B';
	const SUBMISSION_TYPE = 'S';
	
	
	public function __toString()
	{
		return $this->filename;
	}
	
	
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
			array('filename', 'file', 'types'=>array('dem', 'rar', 'zip')),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, object_type, object_id, filename', 'safe', 'on'=>'search'),
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
			'ban' => array(self::BELONGS_TO, 'SBBan', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => self::BAN_TYPE)),
			'submission' => array(self::BELONGS_TO, 'SBSubmission', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => self::SUBMISSION_TYPE)),
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

		$criteria->compare('t.id',$this->id,true);
		$criteria->compare('t.object_id',$this->object_id);
		$criteria->compare('t.object_type',$this->object_type,true);
		$criteria->compare('t.filename',$this->filename,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	
	protected function afterSave()
	{
		// Save file
		$file = CUploadedFile::getInstance($this, 'filename');
		if(!empty($file))
		{
			$file->saveAs(Yii::getPathOfAlias('webroot.demos') . '/' . $file);
		}
		
		parent::afterSave();
	}
}