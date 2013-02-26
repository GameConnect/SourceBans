<?php

/**
 * This is the model class for table "{{bans}}".
 *
 * The followings are the available columns in table '{{bans}}':
 * @property integer $id
 * @property integer $type
 * @property string $steam
 * @property string $ip
 * @property string $name
 * @property string $reason
 * @property integer $length
 * @property integer $server_id
 * @property integer $admin_id
 * @property string $admin_ip
 * @property integer $unban_admin_id
 * @property string $unban_reason
 * @property string $unban_time
 * @property string $time
 *
 * The followings are the available model relations:
 * @property SBAdmin $admin
 * @property SBAdmin $unban_admin
 * @property SBBlock[] $blocks
 * @property SBComment[] $comments
 * @property SBCountry $country
 * @property SBDemo[] $demos
 * @property SBProtest[] $protests
 * @property SBServer $server
 */
class SBBan extends CActiveRecord
{
	const STEAM_TYPE   = 0;
	const IP_TYPE      = 1;
	const DEFAULT_TYPE = 0;
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBBan the static model class
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
		return '{{bans}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('type, reason, length, admin_ip', 'required'),
			array('type, length, server_id, admin_id, unban_admin_id', 'numerical', 'integerOnly'=>true),
			array('steam', 'length', 'max'=>32),
			array('ip, admin_ip', 'length', 'max'=>15),
			array('name', 'length', 'max'=>64),
			array('reason, unban_reason', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, steam, ip, name, reason, length, server_id, admin_id, admin_ip, unban_admin_id, unban_reason, unban_time, time', 'safe', 'on'=>'search'),
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
			'unban_admin' => array(self::BELONGS_TO, 'SBAdmin', 'unban_admin_id'),
			'blocks' => array(self::HAS_MANY, 'SBBlock', 'ban_id'),
			'comments' => array(self::HAS_MANY, 'SBComment', 'ban_id'),
			'country' => array(self::BELONGS_TO, 'SBCountry', 'ip'),
			'demos' => array(self::HAS_MANY, 'SBDemo', 'object_id', 'condition' => 'object_type = "B"'),
			'protests' => array(self::HAS_MANY, 'SBProtest', 'ban_id'),
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
			'type' => Yii::t('sourcebans', 'Type'),
			'steam' => Yii::t('sourcebans', 'Steam ID'),
			'ip' => Yii::t('sourcebans', 'IP address'),
			'name' => Yii::t('sourcebans', 'Name'),
			'reason' => Yii::t('sourcebans', 'Reason'),
			'length' => Yii::t('sourcebans', 'Length'),
			'server_id' => Yii::t('sourcebans', 'Server'),
			'admin_id' => Yii::t('sourcebans', 'Admin'),
			'admin_ip' => 'Admin IP address',
			'unban_admin_id' => 'Unbanned by',
			'unban_reason' => Yii::t('sourcebans', 'Unban reason'),
			'unban_time' => 'Unbanned on',
			'time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
			'demo.filename' => Yii::t('sourcebans', 'Demo'),
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search($criteria=array())
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria($criteria);

		$criteria->compare('id',$this->id);
		$criteria->compare('type',$this->type);
		$criteria->compare('steam',$this->steam,true);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('reason',$this->reason,true);
		$criteria->compare('length',$this->length);
		$criteria->compare('server_id',$this->server_id);
		$criteria->compare('admin_id',$this->admin_id);
		$criteria->compare('admin_ip',$this->admin_ip,true);
		$criteria->compare('unban_admin_id',$this->unban_admin_id);
		$criteria->compare('unban_reason',$this->unban_reason,true);
		$criteria->compare('unban_time',$this->unban_time,true);
		$criteria->compare('time',$this->time,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
			'sort'=>array(
				'attributes'=>array(
					'admin.name'=>array(
						'asc'=>'admin.name',
						'desc'=>'admin.name DESC',
					),
					'*',
				),
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
				'condition'=>$t.'.unban_admin_id IS NULL AND ('.$t.'.length = 0 OR '.$t.'.time + '.$t.'.length * 60 > UNIX_TIMESTAMP())',
			),
			'expired'=>array(
				'condition'=>$t.'.length > 0 AND '.$t.'.time + '.$t.'.length * 60 < UNIX_TIMESTAMP()',
			),
			'inactive'=>array(
				'condition'=>$t.'.unban_admin_id IS NOT NULL OR ('.$t.'.length > 0 AND '.$t.'.time + '.$t.'.length * 60 < UNIX_TIMESTAMP())',
			),
			'unbanned'=>array(
				'condition'=>$t.'.unban_admin_id IS NOT NULL',
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
	
	
	public static function getTimes()
	{
		return array(
			0 => Yii::t('sourcebans', 'Permanent'),
			Yii::t('sourcebans', 'minutes') => array(
				1  => Yii::t('sourcebans', '{n} minute|{n} minutes', 1),
				5  => Yii::t('sourcebans', '{n} minute|{n} minutes', 5),
				10 => Yii::t('sourcebans', '{n} minute|{n} minutes', 10),
				15 => Yii::t('sourcebans', '{n} minute|{n} minutes', 15),
				30 => Yii::t('sourcebans', '{n} minute|{n} minutes', 30),
				45 => Yii::t('sourcebans', '{n} minute|{n} minutes', 45),
			),
			Yii::t('sourcebans', 'hours') => array(
				60  => Yii::t('sourcebans', '{n} hour|{n} hours', 1),
				120 => Yii::t('sourcebans', '{n} hour|{n} hours', 2),
				180 => Yii::t('sourcebans', '{n} hour|{n} hours', 3),
				240 => Yii::t('sourcebans', '{n} hour|{n} hours', 4),
				480 => Yii::t('sourcebans', '{n} hour|{n} hours', 8),
				720 => Yii::t('sourcebans', '{n} hour|{n} hours', 12),
			),
			Yii::t('sourcebans', 'days') => array(
				1440 => Yii::t('sourcebans', '{n} day|{n} days', 1),
				2880 => Yii::t('sourcebans', '{n} day|{n} days', 2),
				4320 => Yii::t('sourcebans', '{n} day|{n} days', 3),
				5760 => Yii::t('sourcebans', '{n} day|{n} days', 4),
				7200 => Yii::t('sourcebans', '{n} day|{n} days', 5),
				8640 => Yii::t('sourcebans', '{n} day|{n} days', 6),
			),
			Yii::t('sourcebans', 'weeks') => array(
				10080 => Yii::t('sourcebans', '{n} week|{n} weeks', 1),
				20160 => Yii::t('sourcebans', '{n} week|{n} weeks', 2),
				30240 => Yii::t('sourcebans', '{n} week|{n} weeks', 3),
			),
			Yii::t('sourcebans', 'months') => array(
				43200  => Yii::t('sourcebans', '{n} month|{n} months', 1),
				86400  => Yii::t('sourcebans', '{n} month|{n} months', 2),
				129600 => Yii::t('sourcebans', '{n} month|{n} months', 3),
				259200 => Yii::t('sourcebans', '{n} month|{n} months', 6),
				518400 => Yii::t('sourcebans', '{n} month|{n} months', 12),
			),
		);
	}
	
	public static function getTypes()
	{
		return array(
			self::STEAM_TYPE => Yii::t('sourcebans', 'Steam ID'),
			self::IP_TYPE    => Yii::t('sourcebans', 'IP address'),
		);
	}
}