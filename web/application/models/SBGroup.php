<?php

/**
 * This is the model class for table "{{groups}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{groups}}':
 * @property integer $id ID
 * @property string $name Name
 *
 * The followings are the available model relations:
 * @property SBAdmin[] $admins
 * @property SBGroupPermission[] $permissions
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBGroup extends CActiveRecord
{
	public function __toString()
	{
		return $this->name;
	}
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBGroup the static model class
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
		return '{{groups}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, permissions', 'required'),
			array('name', 'length', 'max'=>32),
			array('name', 'unique'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
			'admins' => array(self::HAS_MANY, 'SBAdmin', 'group_id'),
			'adminsCount' => array(self::STAT, 'SBAdmin', 'group_id'),
			'permissions' => array(self::HAS_MANY, 'SBGroupPermission', 'group_id'),
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
			'adminsCount' => Yii::t('sourcebans', 'Admins in group'),
			'permissions' => Yii::t('sourcebans', 'Web permissions'),
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
	
	
	protected function afterSave()
	{
		SBGroupPermission::model()->deleteAllByAttributes(array('group_id'=>$this->id));
		
		if(in_array('OWNER', (array)$this->permissions))
		{
			$this->permissions = array('OWNER');
		}
		
		foreach((array)$this->permissions as $name)
		{
			$permission           = new SBGroupPermission;
			$permission->group_id = $this->id;
			$permission->name     = $name;
			$permission->save();
		}
	}
}