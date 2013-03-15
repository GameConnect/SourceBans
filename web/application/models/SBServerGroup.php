<?php

/**
 * This is the model class for table "{{server_groups}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{server_groups}}':
 * @property integer $id ID
 * @property string $name Name
 * @property string $flags Flags
 * @property integer $immunity Immunity level
 *
 * The followings are the available model relations:
 * @property SBAdmin[] $admins
 * @property SBServerGroup[] $groups_immune
 * @property SBServerGroupOverride[] $overrides
 * @property SBServer[] $servers
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBServerGroup extends CActiveRecord
{
	public function __toString()
	{
		return $this->name;
	}
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBServerGroup the static model class
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
		return '{{server_groups}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, flags', 'required'),
			array('immunity', 'numerical', 'integerOnly'=>true),
			array('name, flags', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, flags, immunity', 'safe', 'on'=>'search'),
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
			'admins' => array(self::MANY_MANY, 'SBAdmin', '{{admins_server_groups}}(group_id, admin_id)'),
			'adminsCount' => array(self::STAT, 'SBAdmin', '{{admins_server_groups}}(group_id, admin_id)'),
			'groups_immune' => array(self::MANY_MANY, 'SBServerGroup', '{{server_groups_immunity}}(group_id, other_id)'),
			'overrides' => array(self::HAS_MANY, 'SBServerGroupsOverride', 'group_id'),
			'servers' => array(self::MANY_MANY, 'SBServer', '{{servers_server_groups}}(group_id, server_id)'),
			'serversCount' => array(self::STAT, 'SBServer', '{{servers_server_groups}}(group_id, server_id)'),
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
			'flags' => Yii::t('sourcebans', 'Server permissions'),
			'immunity' => Yii::t('sourcebans', 'Immunity level'),
			'adminsCount' => Yii::t('sourcebans', 'Admins in group'),
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
		$criteria->compare('flags',$this->flags,true);
		$criteria->compare('immunity',$this->immunity);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
			'sort'=>array(
				'defaultOrder'=>array(
					'name'=>CSort::SORT_ASC,
				),
			),
		));
	}
	
	
	protected function beforeSave()
	{
		if(strpos($this->flags, SM_ROOT) !== false)
		{
			$this->flags = SM_ROOT;
		}
		
		return parent::beforeSave();
	}
}