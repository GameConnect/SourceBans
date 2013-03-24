<?php

/**
 * This is the model class for table "{{submissions}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{submissions}}':
 * @property integer $id ID
 * @property string $name Name
 * @property string $steam Steam ID
 * @property string $ip IP address
 * @property string $reason Reason
 * @property integer $server_id Server ID
 * @property string $user_name User name
 * @property string $user_email User email address
 * @property string $user_ip User IP address
 * @property boolean $archived Archived
 * @property integer $create_time Date/Time
 *
 * The followings are the available model relations:
 * @property SBComment[] $comments
 * @property SBDemo $demo
 * @property SBServer $server
 *
 * @package sourcebans.models
 * @since 2.0
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
			array('name, reason, user_name, user_email', 'required'),
			array('server_id', 'numerical', 'integerOnly'=>true),
			array('archived', 'boolean'),
			array('name, user_name', 'length', 'max'=>64),
			array('steam, ip', 'default', 'setOnEmpty'=>true),
			array('steam', 'match', 'pattern'=>SourceBans::STEAM_PATTERN),
			array('ip', 'match', 'pattern'=>SourceBans::IP_PATTERN),
			array('steam', 'unique', 'message'=>Yii::t('sourcebans','{attribute} "{value}" has already been banned.'), 'criteria'=>array(
				'scopes'=>'active',
			)),
			array('ip', 'unique', 'message'=>Yii::t('sourcebans','{attribute} "{value}" has already been banned.'), 'criteria'=>array(
				'scopes'=>'active',
			)),
			array('reason', 'length', 'max'=>255),
			array('user_email', 'length', 'max'=>128),
			array('user_email', 'email'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, steam, ip, reason, server_id, user_name, user_email, user_ip, archived, create_time', 'safe', 'on'=>'search'),
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
			'comments' => array(self::HAS_MANY, 'SBComment', 'object_id', 'condition' => 'object_type = :object_type', params => array(':object_type' => SBComment::SUBMISSION_TYPE)),
			'demo' => array(self::BELONGS_TO, 'SBDemo', 'object_id', 'condition' => 'object_type = :object_type', params => array(':object_type' => SBDemo::SUBMISSION_TYPE)),
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
			'user_name' => Yii::t('sourcebans', 'Your name'),
			'user_email' => Yii::t('sourcebans', 'Your email address'),
			'user_ip' => 'User IP address',
			'archived' => Yii::t('sourcebans', 'Archived'),
			'create_time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
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

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.name',$this->name,true);
		$criteria->compare('t.steam',$this->steam,true);
		$criteria->compare('t.ip',$this->ip,true);
		$criteria->compare('t.reason',$this->reason,true);
		$criteria->compare('t.server_id',$this->server_id);
		$criteria->compare('t.user_name',$this->user_name,true);
		$criteria->compare('t.user_email',$this->user_email,true);
		$criteria->compare('t.user_ip',$this->user_ip,true);
		$criteria->compare('t.archived',$this->archived);
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
				'updateAttribute' => null,
			),
		);
	}
	
	protected function beforeSave()
	{
		if($this->isNewRecord)
		{
			$this->user_ip = Yii::app()->request->userHostAddress;
		}
		if(!empty($this->steam))
		{
			$this->steam = strtoupper($this->steam);
		}
		
		return parent::beforeSave();
	}
}