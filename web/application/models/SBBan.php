<?php

/**
 * This is the model class for table "{{bans}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{bans}}':
 * @property integer $id ID
 * @property integer $type Type
 * @property string $steam Steam ID
 * @property string $ip IP address
 * @property string $name Name
 * @property string $reason Reason
 * @property integer $length Length
 * @property integer $server_id Server ID
 * @property integer $admin_id Admin ID
 * @property string $admin_ip Admin IP address
 * @property integer $unban_admin_id Unbanned by
 * @property string $unban_reason Unban reason
 * @property integer $unban_time Unbanned on
 * @property integer $create_time Date/Time
 * @property SteamProfile $community Steam Community data
 * @property integer $communityId Steam Community ID
 * @property object $country Country data
 * @property boolean $isActive Whether the ban is active
 * @property boolean $isExpired Whether the ban is expired
 * @property boolean $isPermanent Whether the ban is permanent
 * @property boolean $isUnbanned Whether the ban is unbanned
 *
 * The followings are the available model relations:
 * @property SBAdmin $admin Admin
 * @property SBBlock[] $blocks Blocks
 * @property SBComment[] $comments Comments
 * @property SBDemo[] $demos Demos
 * @property SBProtest[] $protests Protests
 * @property SBServer $server Server
 * @property SBAdmin $unban_admin Unban admin
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBBan extends CActiveRecord
{
	const TYPE_STEAM   = 0;
	const TYPE_IP      = 1;
	const TYPE_DEFAULT = 0;
	
	/**
	 * @var integer Steam Community ID
	 */
	protected $ban_community_id;
	
	private $_community;
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBBan the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	public function init()
	{
		$this->attachEventHandler('onAfterDelete', array($this, '_onAfterDelete'));
		$this->attachEventHandler('onAfterSave',   array($this, '_onAfterSave'));
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
		$rules = array(
			array('type, reason, length', 'required'),
			array('type, length', 'numerical', 'integerOnly'=>true),
			array('steam, ip, name', 'default', 'setOnEmpty'=>true),
			array('steam, ip', 'application.validators.SBBanTypeValidator'),
			array('name', 'length', 'max'=>64),
			array('reason, unban_reason', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, steam, ip, name, reason, length, server_id, admin_id, admin_ip, unban_admin_id, unban_reason, unban_time, create_time', 'safe', 'on'=>'search'),
		);
		
		if($this->isNewRecord || $this->isActive)
		{
			$rules[] = array('steam', 'unique', 'message'=>Yii::t('sourcebans','models.SBBan.rules.err_unique'), 'criteria'=>array(
				'condition'=>'type = :type',
				'params'=>array(':type'=>self::TYPE_STEAM),
				'scopes'=>'active',
			));
			$rules[] = array('ip', 'unique', 'message'=>Yii::t('sourcebans','models.SBBan.rules.err_unique'), 'criteria'=>array(
				'condition'=>'type = :type',
				'params'=>array(':type'=>self::TYPE_IP),
				'scopes'=>'active',
			));
		}
		
		return $rules;
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
			'blocks' => array(self::HAS_MANY, 'SBBlock', 'ban_id'),
			'comments' => array(self::HAS_MANY, 'SBComment', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => SBComment::TYPE_BAN)),
			'commentsCount' => array(self::STAT, 'SBComment', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => SBComment::TYPE_BAN)),
			'demos' => array(self::HAS_MANY, 'SBDemo', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => SBDemo::TYPE_BAN)),
			'protests' => array(self::HAS_MANY, 'SBProtest', 'ban_id'),
			'server' => array(self::BELONGS_TO, 'SBServer', 'server_id'),
			'unban_admin' => array(self::BELONGS_TO, 'SBAdmin', 'unban_admin_id'),
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
			'create_time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
			'communityId' => Yii::t('sourcebans', 'Steam Community ID'),
			'admin.name' => Yii::t('sourcebans', 'Admin'),
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

		if(array_key_exists('search', $criteria))
		{
			$search=$criteria['search'];
			unset($criteria['search']);
		}
		$criteria=new CDbCriteria($criteria);
		$criteria->with=array('admin','server');

		if(isset($search))
		{
			$criteria->compare('t.steam', $search, true, 'OR');
			$criteria->compare('t.ip', $search, true, 'OR');
			$criteria->compare('t.name', $search, true, 'OR');
		}
		$criteria->compare('t.id', $this->id);
		$criteria->compare('t.type', $this->type);
		$criteria->compare('t.steam', $this->steam, true);
		$criteria->compare('t.ip', $this->ip, true);
		$criteria->compare('t.name', $this->name, true);
		$criteria->compare('t.reason', $this->reason, true);
		$criteria->compare('t.length', $this->length);
		$criteria->compare('t.server_id', $this->server_id);
		$criteria->compare('t.admin_id', $this->admin_id);
		$criteria->compare('t.admin_ip', $this->admin_ip, true);
		$criteria->compare('t.unban_admin_id', $this->unban_admin_id);
		$criteria->compare('t.unban_reason', $this->unban_reason, true);
		$criteria->compare('t.unban_time', $this->unban_time);
		$criteria->compare('t.create_time', $this->create_time);

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
				'condition'=>$t.'.unban_time IS NULL AND ('.$t.'.length = 0 OR '.$t.'.create_time + '.$t.'.length * 60 > UNIX_TIMESTAMP())',
			),
			'expired'=>array(
				'condition'=>$t.'.length > 0 AND '.$t.'.create_time + '.$t.'.length * 60 < UNIX_TIMESTAMP()',
			),
			'inactive'=>array(
				'condition'=>$t.'.unban_time IS NOT NULL OR ('.$t.'.length > 0 AND '.$t.'.create_time + '.$t.'.length * 60 < UNIX_TIMESTAMP())',
			),
			'permanent'=>array(
				'condition'=>$t.'.length = 0',
			),
			'unbanned'=>array(
				'condition'=>$t.'.unban_time IS NOT NULL',
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
			'UserIdBehavior' => array(
				'class' => 'application.behaviors.UserIdBehavior',
				'attributes' => 'admin_id',
			),
			'UserIpBehavior' => array(
				'class' => 'application.behaviors.UserIpBehavior',
				'attributes' => 'admin_ip',
			),
		);
	}
	
	/**
	 * Returns the Steam Community data
	 * 
	 * @return SteamProfile the Steam Community data
	 */
	public function getCommunity()
	{
		if(empty($this->communityId))
			return null;
		
		if(!isset($this->_community))
		{
			$this->_community = new SteamProfile($this->communityId);
		}
		
		return $this->_community;
	}
	
	/**
	 * Returns the Steam Community ID
	 * 
	 * @return integer the Steam Community ID
	 */
	public function getCommunityId()
	{
		return $this->ban_community_id;
	}
	
	/**
	 * Returns the country data
	 * 
	 * @return object the country data
	 */
	public function getCountry()
	{
		try
		{
			if(empty($this->ip))
				throw new CException;
			
			$code = Yii::app()->geoip->lookupCountryCode($this->ip);
			$name = Yii::app()->geoip->lookupCountryName($this->ip);
			
			if(empty($code) || empty($name))
				throw new CException;
		}
		catch(CException $e)
		{
			return null;
		}
		
		return (object)array(
			'code' => strtolower($code),
			'name' => $name,
		);
	}
	
	/**
	 * Returns whether the ban is active
	 * 
	 * @return boolean whether the ban is active
	 */
	public function getIsActive()
	{
		return !$this->unban_time && (!$this->length || $this->create_time + $this->length * 60 > time());
	}
	
	/**
	 * Returns whether the ban is expired
	 * 
	 * @return boolean whether the ban is expired
	 */
	public function getIsExpired()
	{
		return $this->length && $this->create_time + $this->length * 60 < time();
	}
	
	/**
	 * Returns whether the ban is permanent
	 * 
	 * @return boolean whether the ban is permanent
	 */
	public function getIsPermanent()
	{
		return !$this->length;
	}
	
	/**
	 * Returns whether the ban is unbanned
	 * 
	 * @return boolean whether the ban is unbanned
	 */
	public function getIsUnbanned()
	{
		return !!$this->unban_time;
	}
	
	/**
	 * Unbans the ban
	 * 
	 * @param string $reason optional unban reason
	 * @return boolean whether the unbanning is successful
	 */
	public function unban($reason = null)
	{
		$this->unban_admin_id = Yii::app()->user->id;
		$this->unban_reason   = $reason;
		$this->unban_time     = time();
		
		return $this->save(false);
	}
	
	
	protected function _onAfterDelete($event)
	{
		SourceBans::app()->trigger('bans.deleteBan', $event);
	}
	
	protected function _onAfterSave($event)
	{
		SourceBans::app()->trigger('bans.saveBan', $event);
	}
	
	
	/**
	 * Returns a list of common ban lengths
	 * 
	 * @return array a list of common ban lengths
	 */
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
	
	/**
	 * Returns the supported ban types
	 * 
	 * @return array the supported ban types
	 */
	public static function getTypes()
	{
		return array(
			self::TYPE_STEAM => Yii::t('sourcebans', 'Steam ID'),
			self::TYPE_IP    => Yii::t('sourcebans', 'IP address'),
		);
	}
	
	
	protected function beforeFind()
	{
		$t = $this->tableAlias;
		
		// Select community ID
		$select=array(
			'76561197960265728 + CAST(MID('.$t.'.steam, 9, 1) AS UNSIGNED) + CAST(MID('.$t.'.steam, 11, 10) * 2 AS UNSIGNED) AS ban_community_id',
		);
		if($this->dbCriteria->select==='*')
		{
			array_unshift($select,'*');
		}
		$this->dbCriteria->mergeWith(array(
			'select'=>$select,
		));
		
		parent::beforeFind();
	}
	
	protected function beforeSave()
	{
		if(!empty($this->steam))
		{
			$this->steam = strtoupper($this->steam);
		}
		
		return parent::beforeSave();
	}
}