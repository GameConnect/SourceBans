<?php

/**
 * This is the model class for table "{{protests}}".
 *
 * The followings are the available columns in table '{{protests}}':
 * @property integer $id
 * @property integer $ban_id
 * @property string $reason
 * @property string $email
 * @property string $ip
 * @property integer $archived
 * @property string $time
 *
 * The followings are the available model relations:
 * @property SBBan $ban
 */
class SBProtest extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBProtest the static model class
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
		return '{{protests}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ban_id, reason, email, ip, time', 'required'),
			array('ban_id, archived', 'numerical', 'integerOnly'=>true),
			array('reason', 'length', 'max'=>255),
			array('email', 'length', 'max'=>128),
			array('ip', 'length', 'max'=>15),
			array('time', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, ban_id, reason, email, ip, archived, time', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'ban_id' => Yii::t('sourcebans', 'Ban'),
			'reason' => Yii::t('sourcebans', 'Reason'),
			'email' => Yii::t('sourcebans', 'Email address'),
			'ip' => Yii::t('sourcebans', 'IP address'),
			'archived' => Yii::t('sourcebans','Archived'),
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
		$criteria->compare('ban_id',$this->ban_id);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('archived',$this->archived);
		$criteria->compare('time',$this->time,true);

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

	public function scopes()
	{
		$t = $this->tableAlias;
		
		return array(
			'active'=>array(
				'condition'=>$t.'.archived = 0',
			),
			'archived'=>array(
				'condition'=>$t.'.archived = 1',
			),
		);
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