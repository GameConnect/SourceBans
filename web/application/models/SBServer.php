<?php

/**
 * This is the model class for table "{{servers}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{servers}}':
 * @property integer $id ID
 * @property string $ip IP address
 * @property integer $port Port
 * @property string $rcon RCON password
 * @property integer $game_id Game ID
 * @property boolean $enabled Enabled
 *
 * The followings are the available model relations:
 * @property SBAction[] $actions
 * @property SBBan[] $bans
 * @property SBBlock[] $blocks
 * @property SBGame $game
 * @property SBServerGroup[] $groups
 * @property SBSubmission[] $submissions
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBServer extends CActiveRecord
{
	public function __toString()
	{
		return $this->address;
	}
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBServer the static model class
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
		return '{{servers}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ip, game_id', 'required'),
			array('port, game_id', 'numerical', 'integerOnly'=>true),
			array('enabled', 'boolean'),
			array('ip', 'match', 'pattern'=>'/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/'),
			array('rcon', 'length', 'max'=>32),
			array('groups', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, ip, port, rcon, game_id, enabled', 'safe', 'on'=>'search'),
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
			'actions' => array(self::HAS_MANY, 'SBAction', 'server_id'),
			'bans' => array(self::HAS_MANY, 'SBBan', 'server_id'),
			'blocks' => array(self::HAS_MANY, 'SBBlock', 'server_id'),
			'game' => array(self::BELONGS_TO, 'SBGame', 'game_id'),
			'groups' => array(self::MANY_MANY, 'SBServerGroup', '{{servers_server_groups}}(server_id, group_id)'),
			'submissions' => array(self::HAS_MANY, 'SBSubmission', 'server_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'ip' => Yii::t('sourcebans', 'IP address'),
			'port' => Yii::t('sourcebans', 'Port'),
			'rcon' => Yii::t('sourcebans', 'RCON password'),
			'game_id' => Yii::t('sourcebans', 'Game'),
			'enabled' => Yii::t('sourcebans', 'Enabled'),
			'game.name' => Yii::t('sourcebans', 'Game'),
			'groups.name' => Yii::t('sourcebans', 'Server groups'),
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
		$criteria->with='game';

		$criteria->compare('t.id',$this->id);
		$criteria->compare('t.ip',$this->ip,true);
		$criteria->compare('t.port',$this->port);
		$criteria->compare('t.rcon',$this->rcon,true);
		$criteria->compare('t.game_id',$this->game_id);
		$criteria->compare('t.enabled',$this->enabled);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
			'sort'=>array(
				'attributes'=>array(
					'game.name'=>array(
						'asc'=>'game.name',
						'desc'=>'game.name DESC',
					),
					'*',
				),
				'defaultOrder'=>array(
					'game.name'=>CSort::SORT_ASC,
					'ip'=>CSort::SORT_ASC,
					'port'=>CSort::SORT_ASC,
				),
			),
		));
	}

	public function scopes()
	{
		$t = $this->tableAlias;
		
		return array(
			'disabled'=>array(
				'condition'=>$t.'.enabled = 0',
			),
			'enabled'=>array(
				'condition'=>$t.'.enabled = 1',
			),
		);
	}
	
	public function behaviors()
	{
		return array(
			'EActiveRecordRelationBehavior'=>array(
				'class'=>'ext.EActiveRecordRelationBehavior',
			),
		);
	}
	
	public function getAddress()
	{
		return $this->ip . ':' . $this->port;
	}
}