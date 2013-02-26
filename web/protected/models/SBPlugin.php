<?php

/**
 * This is the model class for table "{{plugins}}".
 *
 * The followings are the available columns in table '{{plugins}}':
 * @property string $name
 * @property integer $enabled
 */
class SBPlugin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBPlugin the static model class
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
		return '{{plugins}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name', 'required'),
			array('enabled', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('name, enabled', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'name' => Yii::t('sourcebans', 'Name'),
			'enabled' => Yii::t('sourcebans', 'Enabled'),
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

		$criteria->compare('name',$this->name,true);
		$criteria->compare('enabled',$this->enabled);

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
	
	/**
	 * @return string the name of this SourceBans plugin
	 */
	public function getName() {}
	
	/**
	 * @return string the description of this SourceBans plugin
	 */
	public function getDescription() {}
	
	/**
	 * @return string the author of this SourceBans plugin
	 */
	public function getAuthor() {}
	
	/**
	 * @return string the version of this SourceBans plugin
	 */
	public function getVersion() {}
	
	/**
	 * @return string the URL of this SourceBans plugin
	 */
	public function getUrl() {}
}