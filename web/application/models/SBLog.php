<?php

/**
 * This is the model class for table "{{logs}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{logs}}':
 * @property integer $id ID
 * @property string $type Type
 * @property string $title Title
 * @property string $message Message
 * @property string $function Function
 * @property string $query Query
 * @property integer $admin_id Admin ID
 * @property string $admin_ip Admin IP address
 * @property integer $create_time Date/Time
 *
 * The followings are the available model relations:
 * @property SBAdmin $admin Admin
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBLog extends CActiveRecord
{
	const TYPE_ERROR       = 'e';
	const TYPE_INFORMATION = 'm';
	const TYPE_WARNING     = 'w';
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBLog the static model class
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
		return '{{logs}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, type, title, message, function, query, admin_id, admin_ip, create_time', 'safe', 'on'=>'search'),
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
			'title' => Yii::t('sourcebans', 'Title'),
			'message' => Yii::t('sourcebans', 'Message'),
			'function' => Yii::t('sourcebans', 'Function'),
			'query' => Yii::t('sourcebans', 'Query'),
			'admin_id' => Yii::t('sourcebans', 'Admin'),
			'admin_ip' => 'Admin IP address',
			'create_time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
			'admin.name' => Yii::t('sourcebans', 'Admin'),
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
		$criteria->with='admin';

		$criteria->compare('t.id', $this->id);
		$criteria->compare('t.type', $this->type);
		$criteria->compare('t.title', $this->title, true);
		$criteria->compare('t.message', $this->message, true);
		$criteria->compare('t.function', $this->function, true);
		$criteria->compare('t.query', $this->query, true);
		$criteria->compare('t.admin_id', $this->admin_id);
		$criteria->compare('t.admin_ip', $this->admin_ip, true);
		$criteria->compare('t.create_time', $this->create_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
			'sort'=>array(
				'defaultOrder'=>array(
					'create_time'=>CSort::SORT_DESC,
				),
			),
		));
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
	
	
	public static function getTypes()
	{
		return array(
			self::TYPE_ERROR       => Yii::t('sourcebans', 'Error'),
			self::TYPE_INFORMATION => Yii::t('sourcebans', 'Information'),
			self::TYPE_WARNING     => Yii::t('sourcebans', 'Warning'),
		);
	}
	
	
	protected function beforeSave()
	{
		if($this->isNewRecord)
		{
			$this->function = $this->_getTraces();
			$this->query    = Yii::app()->request->queryString;
		}
		
		return parent::beforeSave();
	}
	
	
	private function _getTraces($level = 5)
	{
		$traces = array_slice(debug_backtrace(), 3); // Strip first 3 traces
		$count  = 0;
		$ret    = '';
		
		foreach($traces as $trace)
		{
			if(isset($trace['file'], $trace['line']))
			{
				$ret .= $trace['file'] . ' (' . $trace['line'] . ")\n";
				if(++$count>=$level)
					break;
			}
		}
		
		return trim($ret);
	}
}