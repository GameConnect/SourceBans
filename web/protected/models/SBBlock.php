<?php

/**
 * This is the model class for table "{{blocks}}".
 *
 * The followings are the available columns in table '{{blocks}}':
 * @property integer $ban_id
 * @property string $name
 * @property integer $server_id
 * @property string $time
 *
 * The followings are the available model relations:
 * @property SBBan $ban
 * @property SBServer $server
 */
class SBBlock extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Blocks the static model class
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
		return array('ban_id', 'name', 'server_id', 'time');
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ban_id, name, server_id, time', 'required'),
			array('ban_id, server_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>64),
			array('time', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ban_id, name, server_id, time', 'safe', 'on'=>'search'),
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

		$criteria->compare('ban_id',$this->ban_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('server_id',$this->server_id);
		$criteria->compare('time',$this->time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}