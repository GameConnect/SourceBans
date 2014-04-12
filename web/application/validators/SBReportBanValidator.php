<?php
/**
 * SourceBans report ban validator
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.components
 * @since 2.0
 */
class SBReportBanValidator extends CValidator
{
	/**
	 * @var boolean whether the comparison is case sensitive. Defaults to true.
	 * Note, by setting it to false, you are assuming the attribute type is string.
	 */
	public $caseSensitive=true;
	/**
	 * @var boolean whether the attribute value can be null or empty. Defaults to true,
	 * meaning that if the attribute is empty, it is considered valid.
	 */
	public $allowEmpty=true;
	/**
	 * @var string the ActiveRecord class name that should be used to
	 * look for the attribute value being validated. Defaults to null, meaning using
	 * the class of the object currently being validated.
	 * You may use path alias to reference a class name here.
	 * @see attributeName
	 */
	public $className;
	/**
	 * @var string the ActiveRecord class attribute name that should be
	 * used to look for the attribute value being validated. Defaults to null,
	 * meaning using the name of the attribute being validated.
	 * @see className
	 */
	public $attributeName;
	/**
	 * @var mixed additional query criteria. Either an array or CDbCriteria.
	 * This will be combined with the condition that checks if the attribute
	 * value exists in the corresponding table column.
	 * This array will be used to instantiate a {@link CDbCriteria} object.
	 */
	public $criteria=array();
	/**
	 * @var string the user-defined error message. The placeholders "{attribute}" and "{value}"
	 * are recognized, which will be replaced with the actual attribute name and value, respectively.
	 */
	public $message;
	/**
	 * @var boolean whether this validation rule should be skipped if when there is already a validation
	 * error for the current attribute. Defaults to true.
	 */
	public $skipOnError=true;
	
	
	/**
	 * Validates the attribute of the object.
	 * If there is any error, the error message is added to the object.
	 * @param CModel $object the object being validated
	 * @param string $attribute the attribute being validated
	 */
	protected function validateAttribute($object, $attribute)
	{
		if($this->isEmpty($object->steam, true) && $this->isEmpty($object->ip, true))
			return $this->addError($object, $attribute, Yii::t('yii','{attribute} cannot be blank.'));
		
		$value=$object->$attribute;
		if($this->allowEmpty && $this->isEmpty($value))
			return;
		
		if($attribute == 'steam')
		{
			$criteria = new CDbCriteria(array(
				'condition'=>'type = :type',
				'params'=>array(':type'=>SBBan::TYPE_STEAM),
			));
		}
		else if($attribute == 'ip')
		{
			$criteria = new CDbCriteria(array(
				'condition'=>'type = :type',
				'params'=>array(':type'=>SBBan::TYPE_IP),
			));
		}
		else
			return;
		
		$className=$this->className===null?get_class($object):Yii::import($this->className);
		$attributeName=$this->attributeName===null?$attribute:$this->attributeName;
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
		
		if(!$object instanceof CActiveRecord || $object->isNewRecord || $object->tableName()!==$finder->tableName())
			$exists=$finder->exists($criteria);
		else
		{
			$criteria->limit=2;
			$objects=$finder->findAll($criteria);
			$n=count($objects);
			if($n===1)
			{
				if($column->isPrimaryKey)  // primary key is modified and not unique
					$exists=$object->getOldPrimaryKey()!=$object->getPrimaryKey();
				else
				{
					// non-primary key, need to exclude the current record based on PK
					$exists=array_shift($objects)->getPrimaryKey()!=$object->getOldPrimaryKey();
				}
			}
			else
				$exists=$n>1;
		}
		
		if($exists)
		{
			$message=$this->message!==null?$this->message:Yii::t('yii','{attribute} "{value}" has already been taken.');
			$this->addError($object,$attribute,$message,array('{value}'=>CHtml::encode($value)));
		}
	}
}