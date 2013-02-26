<?php

/**
 * This is the model class for table "{{servers}}".
 *
 * The followings are the available columns in table '{{servers}}':
 * @property integer $id
 * @property string $ip
 * @property integer $port
 * @property string $rcon
 * @property integer $game_id
 * @property integer $enabled
 *
 * The followings are the available model relations:
 * @property SBAction[] $actions
 * @property SBBan[] $bans
 * @property SBBlock[] $blocks
 * @property SBGame $game
 * @property SBServerGroup[] $groups
 * @property SBSubmission[] $submissions
 */
class SBServer extends CActiveRecord
{
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
			array('port, game_id, enabled', 'numerical', 'integerOnly'=>true),
			array('ip', 'length', 'max'=>15),
			array('rcon', 'length', 'max'=>32),
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

		$criteria->compare('id',$this->id);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('port',$this->port);
		$criteria->compare('rcon',$this->rcon,true);
		$criteria->compare('game_id',$this->game_id);
		$criteria->compare('enabled',$this->enabled);

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
	
	public function getAddress()
	{
		return $this->ip . ':' . $this->port;
	}
}