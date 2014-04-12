<?php
/**
 * SourceBans appeal ban validator
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SBAppealBanValidator extends CValidator
{
	/**
	 * @var boolean whether the comparison is case sensitive. Defaults to true.
	 * Note, by setting it to false, you are assuming the attribute type is string.
	 */
	public $caseSensitive=true;
	/**
	 * @var string the ActiveRecord class name that should be used to
	 * look for the attribute value being validated. Defaults to null,
	 * meaning using the ActiveRecord class of the attribute being validated.
	 * You may use path alias to reference a class name here.
	 * @see attributeName
	 */
	public $className;
	/**
	 * @var mixed additional query criteria. Either an array or CDbCriteria.
	 * This will be combined with the condition that checks if the attribute
	 * value exists in the corresponding table column.
	 * This array will be used to instantiate a {@link CDbCriteria} object.
	 */
	public $criteria=array();
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	
	
	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object, $attribute)
	{
		if($this->isEmpty($object->ban_steam, true) && $this->isEmpty($object->ban_ip, true))
			return $this->addError($object, $attribute, Yii::t('yii','{attribute} cannot be blank.'));
		
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		
		if($attribute == 'ban_steam')
		{
			$attributeName = 'steam';
			$criteria = new CDbCriteria(array(
				'condition'=>'type = :type',
				'params'=>array(':type'=>SBBan::TYPE_STEAM),
				'with'=>array('appeals'=>array('select'=>false)),
			));
		}
		else if($attribute == 'ban_ip')
		{
			$attributeName = 'ip';
			$criteria = new CDbCriteria(array(
				'condition'=>'type = :type',
				'params'=>array(':type'=>SBBan::TYPE_IP),
				'with'=>array('appeals'=>array('select'=>false)),
			));
		}
		else
			return;
		
		$className=$this->className===null?get_class($object):Yii::import($this->className);
		$finder=CActiveRecord::model($className);
		$table=$finder->getTableSchema();
		if(($column=$table->getColumn($attributeName))===null)
			throw new CException(Yii::t('yii','Table "{table}" does not have a column named "{column}".',
				array('{column}'=>$attributeName,'{table}'=>$table->name)));
		
		$columnName=$column->rawName;
		if($this->criteria!==array())
			$criteria->mergeWith($this->criteria);
		$tableAlias = empty($criteria->alias) ? $finder->getTableAlias(true) : $criteria->alias;
		$valueParamName = CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount++;
		$criteria->addCondition($this->caseSensitive ? "{$tableAlias}.{$columnName}={$valueParamName}" : "LOWER({$tableAlias}.{$columnName})=LOWER({$valueParamName})");
		$criteria->params[$valueParamName] = $value;
		
		if(($ban=$finder->find($criteria))===null)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} "{value}" is invalid.');
			$this->addError($object,$attribute,$message,array('{value}'=>CHtml::encode($value)));
		}
		else if(!empty($ban->appeals))
		{
			$message=Yii::t('sourcebans','components.SBAppealBanValidator.error');
			$this->addError($object,$attribute,$message,array('{value}'=>CHtml::encode($value)));
		}
		else
		{
			$object->ban_id = $ban->id;
		}
	}
}