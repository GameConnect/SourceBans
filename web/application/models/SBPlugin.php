<?php

/**
 * This is the model class for table "{{plugins}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{plugins}}':
 * @property string $class Class
 * @property boolean $enabled Enabled
 *
 * @package sourcebans.models
 * @since 2.0
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
			array('class', 'required'),
			array('enabled', 'boolean'),
			array('class', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('class, enabled', 'safe', 'on'=>'search'),
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
			'class' => 'Class',
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

		$criteria->compare('class',$this->class,true);
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
	 * Returns the name of this SourceBans plugin
	 * 
	 * @return string the name of this SourceBans plugin
	 */
	public function getName() {}
	
	/**
	 * Returns the description of this SourceBans plugin
	 * 
	 * @return string the description of this SourceBans plugin
	 */
	public function getDescription() {}
	
	/**
	 * Returns the author of this SourceBans plugin
	 * 
	 * @return string the author of this SourceBans plugin
	 */
	public function getAuthor() {}
	
	/**
	 * Returns the version of this SourceBans plugin
	 * 
	 * @return string the version of this SourceBans plugin
	 */
	public function getVersion() {}
	
	/**
	 * Returns the URL of this SourceBans plugin
	 * 
	 * @return string the URL of this SourceBans plugin
	 */
	public function getUrl() {}
	
	/**
	 * Raised right BEFORE the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	public function onBeginRequest($event) {}
	
	/**
	 * Raised right AFTER the application processes the request.
	 * @param CEvent $event the event parameter
	 */
	public function onEndRequest($event) {}
	
	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param CAction $action the action to be executed.
	 */
	public function onBeforeAction($action) {}
	
	/**
	 * This method is invoked at the beginning of {@link CController::render()}.
	 * You may override this method to do some preprocessing when rendering a view.
	 * @param string $view the view to be rendered
	 */
	public function onBeforeRender($view) {}
	
	/**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * @param CAction $action the action just executed.
	 */
	public function onAfterAction($action) {}
	
	/**
	 * This method is invoked after the specified is rendered by calling {@link CController::render()}.
	 * Note that this method is invoked BEFORE {@link CController::processOutput()}.
	 * You may override this method to do some postprocessing for the view rendering.
	 * @param string $view the view that has been rendered
	 * @param string $output the rendering result of the view. Note that this parameter is passed
	 * as a reference. That means you can modify it within this method.
	 */
	public function onAfterRender($view, &$output) {}
	
	
	/**
	 * Creates an active record instance.
	 * This method is called by {@link CActiveRecord::populateRecord} and {@link CActiveRecord::populateRecords}.
	 * @param array $attributes list of attribute values for the active records.
	 * @return CActiveRecord the active record
	 */
	protected function instantiate($attributes)
	{
		try
		{
			$class=Yii::import('application.plugins.'.$attributes['class'], true);
		}
		catch(CException $e)
		{
			// File is not readable, delete plugin
			$this->deleteByPk($attributes['class']);
			
			$class=__CLASS__;
		}
		
		$model=new $class(null);
		return $model;
	}
}