<?php

/**
 * This is the model class for table "{{games}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{games}}':
 * @property integer $id ID
 * @property string $name Name
 * @property string $folder Folder
 * @property string $icon Icon
 *
 * The followings are the available model relations:
 * @property SBServer[] $servers Servers
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBGame extends CActiveRecord
{
	public function __toString()
	{
		return $this->name;
	}
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBGame the static model class
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
		return '{{games}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, folder', 'required'),
			array('name, folder', 'length', 'max'=>32),
			array('name', 'unique'),
			array('icon', 'file', 'types'=>array('gif', 'ico', 'jpg', 'png'), 'on'=>'insert'),
			array('icon', 'file', 'types'=>array('gif', 'ico', 'jpg', 'png'), 'allowEmpty'=>true, 'on'=>'update'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, folder, icon', 'safe', 'on'=>'search'),
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
			'servers' => array(self::HAS_MANY, 'SBServer', 'game_id'),
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
			'folder' => Yii::t('sourcebans', 'Folder'),
			'icon' => Yii::t('sourcebans', 'Icon'),
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
		$criteria->compare('t.folder',$this->folder,true);
		$criteria->compare('t.icon',$this->icon,true);

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
		// Save icon
		$icon = CUploadedFile::getInstance($this, 'icon');
		if(!empty($icon))
		{
			$icon->saveAs(Yii::getPathOfAlias('webroot.images.games') . '/' . $icon);
		}
		
		parent::afterSave();
	}
}