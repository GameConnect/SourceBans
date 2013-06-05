<?php

/**
 * This is the model class for table "{{protests}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{protests}}':
 * @property integer $id ID
 * @property integer $ban_id Ban ID
 * @property string $reason Reason
 * @property string $user_email User email address
 * @property string $user_ip User IP address
 * @property boolean $archived Archived
 * @property integer $create_time Date/Time
 *
 * The followings are the available model relations:
 * @property SBBan $ban
 * @property SBComment[] $comments
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBProtest extends CActiveRecord
{
	public $ban_steam;
	public $ban_ip;
	
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
			array('reason, user_email', 'required'),
			array('archived', 'boolean'),
			array('reason', 'length', 'max'=>255),
			array('user_email', 'length', 'max'=>128),
			array('user_email', 'email'),
			array('ban_steam, ban_ip', 'default', 'setOnEmpty'=>true),
			array('ban_steam', 'match', 'pattern'=>SourceBans::STEAM_PATTERN),
			array('ban_ip', 'match', 'pattern'=>SourceBans::IP_PATTERN),
			array('ban_steam, ban_ip', 'SBProtestBanValidator', 'className'=>'SBBan', 'message'=>Yii::t('sourcebans','{attribute} "{value}" is currently not banned.'), 'criteria'=>array(
				'scopes'=>'active',
			)),
			array('ban_id', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, ban_id, reason, user_email, user_ip, archived, create_time', 'safe', 'on'=>'search'),
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
			'comments' => array(self::HAS_MANY, 'SBComment', 'object_id', 'condition' => 'object_type = :object_type', 'params' => array(':object_type' => SBComment::PROTEST_TYPE)),
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
			'ban_ip' => Yii::t('sourcebans', 'IP address'),
			'ban_steam' => Yii::t('sourcebans', 'Steam ID'),
			'reason' => Yii::t('sourcebans', 'Reason'),
			'user_email' => Yii::t('sourcebans', 'Your email address'),
			'user_ip' => 'User IP address',
			'archived' => Yii::t('sourcebans','Archived'),
			'create_time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
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
		$criteria->with='ban';

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.ban_id',$this->ban_id);
		$criteria->compare('t.reason',$this->reason,true);
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
				'attributes'=>array(
					'ban.name'=>array(
						'asc'=>'ban.name',
						'desc'=>'ban.name DESC',
					),
					'ban.steam'=>array(
						'asc'=>'ban.steam',
						'desc'=>'ban.steam DESC',
					),
					'ban.ip'=>array(
						'asc'=>'ban.ip',
						'desc'=>'ban.ip DESC',
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