<?php

/**
 * This is the model class for table "{{submissions}}".
 *
 * The followings are the available columns in table '{{submissions}}':
 * @property integer $id
 * @property string $name
 * @property string $steam
 * @property string $ip
 * @property string $reason
 * @property integer $server_id
 * @property string $subname
 * @property string $subemail
 * @property string $subip
 * @property integer $archived
 * @property string $time
 *
 * The followings are the available model relations:
 * @property SBDemo $demo
 * @property SBServer $server
 */
class SBSubmission extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBSubmission the static model class
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
		return '{{submissions}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, reason, subname, subemail, subip, time', 'required'),
			array('server_id, archived', 'numerical', 'integerOnly'=>true),
			array('name, subname', 'length', 'max'=>64),
			array('steam', 'length', 'max'=>32),
			array('ip, subip', 'length', 'max'=>15),
			array('reason', 'length', 'max'=>255),
			array('subemail', 'length', 'max'=>128),
			array('time', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, steam, ip, reason, server_id, subname, subemail, subip, archived, time', 'safe', 'on'=>'search'),
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
			'demo' => array(self::BELONGS_TO, 'SBDemo', 'object_id', 'condition' => 'object_type = "S"'),
			'server' => array(self::BELONGS_TO, 'SBServer', 'server_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => Yii::t('sourcebans', 'Name'),
			'steam' => Yii::t('sourcebans', 'Steam ID'),
			'ip' => Yii::t('sourcebans', 'IP address'),
			'reason' => Yii::t('sourcebans', 'Reason'),
			'server_id' => Yii::t('sourcebans', 'Server'),
			'subname' => 'Name',
			'subemail' => 'Email address',
			'subip' => 'IP address',
			'archived' => Yii::t('sourcebans', 'Archived'),
			'time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
			'demo.filename' => Yii::t('sourcebans', 'Demo'),
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('steam',$this->steam,true);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('server_id',$this->server_id);
		$criteria->compare('subname',$this->subname,true);
		$criteria->compare('subemail',$this->subemail,true);
		$criteria->compare('subip',$this->subip,true);
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