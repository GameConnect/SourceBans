<?php

/**
 * This is the model class for table "{{admins}}".
 *
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 *
 * The followings are the available columns in table '{{admins}}':
 * @property integer $id ID
 * @property string $name Name
 * @property string $auth Authentication type
 * @property string $identity Identity
 * @property string $password Password
 * @property string $password_key Password key
 * @property integer $group_id Web Group ID
 * @property string $email Email address
 * @property string $language Language
 * @property string $theme Theme
 * @property string $srv_password Server password
 * @property string $validate Validation key
 * @property integer $lastvisit Last visit
 * @property string $flags Server flags
 *
 * The followings are the available model relations:
 * @property SBAction[] $actions
 * @property SBGroup $group
 * @property SBServerGroup[] $server_groups
 * @property SBBan[] $bans
 * @property SBBan[] $unban_bans
 * @property SBComment[] $comments
 * @property SBComment[] $edit_comments
 * @property SBLog[] $logs
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBAdmin extends CActiveRecord
{
	const IP_AUTH    = 'ip';
	const NAME_AUTH  = 'name';
	const STEAM_AUTH = 'steam';
	
	
	public function __toString()
	{
		return $this->name;
	}
	
	
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return SBAdmin the static model class
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
		return '{{admins}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, auth, identity', 'required'),
			array('group_id', 'numerical', 'integerOnly'=>true),
			array('name, identity', 'length', 'max'=>64),
			array('name', 'unique'),
			array('identity', 'SBAdminIdentityValidator'),
			array('password, srv_password', 'length', 'max'=>64, 'min'=>SourceBans::app()->settings->password_min_length),
			array('email', 'email'),
			array('email', 'length', 'max'=>128),
			array('language', 'length', 'max'=>2),
			array('theme', 'length', 'max'=>32),
			array('server_groups', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, auth, identity, password, group_id, email, language, theme, srv_password, lastvisit', 'safe', 'on'=>'search'),
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
			'actions' => array(self::HAS_MANY, 'SBAction', 'admin_id'),
			'group' => array(self::BELONGS_TO, 'SBGroup', 'group_id'),
			'server_groups' => array(self::MANY_MANY, 'SBServerGroup', '{{admins_server_groups}}(admin_id, group_id)', 'order' => 'inherit_order'),
			'bans' => array(self::HAS_MANY, 'SBBan', 'admin_id'),
			'unban_bans' => array(self::HAS_MANY, 'SBBan', 'unban_admin_id'),
			'comments' => array(self::HAS_MANY, 'SBComment', 'admin_id'),
			'edit_comments' => array(self::HAS_MANY, 'SBComment', 'edit_admin_id'),
			'logs' => array(self::HAS_MANY, 'SBLog', 'admin_id'),
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
			'auth' => Yii::t('sourcebans', 'Authentication type'),
			'identity' => Yii::t('sourcebans', 'Identity'),
			'password' => Yii::t('sourcebans', 'Password'),
			'group_id' => Yii::t('sourcebans', 'Web group'),
			'email' => Yii::t('sourcebans', 'Email address'),
			'language' => Yii::t('sourcebans', 'Language'),
			'theme' => Yii::t('sourcebans', 'Theme'),
			'srv_password' => Yii::t('sourcebans', 'Server password'),
			'validate' => 'Validation key',
			'lastvisit' => Yii::t('sourcebans', 'Last visit'),
			'group.name' => Yii::t('sourcebans', 'Web group'),
			'server_groups.name' => Yii::t('sourcebans', 'Server groups'),
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
		$criteria->with='group';

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('auth',$this->auth,true);
		$criteria->compare('identity',$this->identity,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('group_id',$this->group_id);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('language',$this->language,true);
		$criteria->compare('theme',$this->theme,true);
		$criteria->compare('srv_password',$this->srv_password,true);
		$criteria->compare('lastvisit',$this->lastvisit);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>SourceBans::app()->settings->items_per_page,
			),
			'sort'=>array(
				'attributes'=>array(
					'group.name'=>array(
						'asc'=>'group.name',
						'desc'=>'group.name DESC',
					),
					'*',
				),
				'defaultOrder'=>array(
					'name'=>CSort::SORT_ASC,
				),
			),
		));
	}
	
	public function behaviors()
	{
		return array(
			'EActiveRecordRelationBehavior'=>array(
				'class'=>'ext.EActiveRecordRelationBehavior',
			),
		);
	}
	
	/**
	 * Returns the server flags of the admin
	 * 
	 * @return string the server flags of the admin
	 */
	public function getFlags()
	{
		$flags = '';
		foreach($this->server_groups as $server_group)
		{
			$flags .= $server_group->flags;
		}
		
		return count_chars($flags, 3);
	}
	
	/**
	 * Returns whether the admin has one of these server flags
	 * 
	 * @param mixed $flag Flag(s) to check for
	 * @return boolean
	 */
	public function hasFlag($flag)
	{
		if(func_num_args() > 1)
			$flags = func_get_args();
		else if(is_array($flag))
			$flags = $flag;
		else
			$flags = str_split($flag);
		
		foreach($this->server_groups as $server_group)
		{
			if(strpos($server_group->flags, SM_ROOT) !== false)
				return true;
			
			foreach($flags as $flag)
			{
				if(strpos($server_group->flags, $flag) !== false)
					return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Returns whether the admin has all of these server flags
	 * 
	 * @param mixed $flag Flag(s) to check for
	 * @return boolean
	 */
	public function hasFlags($flag)
	{
		if(func_num_args() > 1)
			$flags = func_get_args();
		else if(is_array($flag))
			$flags = $flag;
		else
			$flags = str_split($flag);
		
		foreach($this->server_groups as $server_group)
		{
			if(strpos($server_group->flags, SM_ROOT) !== false)
				return true;
			
			foreach($flags as $flag)
			{
				if(strpos($server_group->flags, $flag) === false)
					return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Returns whether the admin has one of these web permissions
	 * 
	 * @param mixed $name Permission name(s) to check for
	 * @return boolean
	 */
	public function hasPermission($name)
	{
		$permissions = CHtml::listData($this->group->permissions, 'name', 'name');
		if(isset($permissions['OWNER']))
			return true;
		
		$names = is_array($name) ? $name : func_get_args();
		foreach($names as $name)
		{
			if(isset($permissions[$name]))
				return true;
		}
		
		return false;
	}
	
	/**
	 * Returns whether the admin has all of these web permissions
	 *
	 * @param mixed $name Permission name(s) to check for
	 * @return boolean
	 */
	public function hasPermissions($name)
	{
		$permissions = CHtml::listData($this->group->permissions, 'name', 'name');
		if(isset($permissions['OWNER']))
			return true;
		
		$names = is_array($name) ? $name : func_get_args();
		foreach($names as $name)
		{
			if(!isset($permissions[$name]))
				return false;
		}
		
		return true;
	}
	
	public function setPassword($password)
	{
		$this->password_key = self::getPasswordKey();
		$this->password = self::getPasswordHash($password, $this->password_key);
	}
	
	public function validatePassword($password)
	{
		return $this->password == self::getPasswordHash($password, $this->password_key);
	}
	
	
	/**
	 * Returns the supported authentication types
	 * 
	 * @return array the supported authentication types
	 */
	public static function getAuthTypes()
	{
		return array(
			self::STEAM_AUTH => Yii::t('sourcebans', 'Steam ID'),
			self::IP_AUTH    => Yii::t('sourcebans', 'IP address'),
			self::NAME_AUTH  => Yii::t('sourcebans', 'Name'),
		);
	}
	
	/**
	 * Returns a random hash based on a password and a password key
	 * 
	 * @param string $password the password
	 * @param string $key the password key
	 * @return string a random hash
	 */
	public static function getPasswordHash($password, $key = null)
	{
		if(empty($password))
			return null;
		
		// Backwards compatibility with old password format
		if(empty($key))
			return sha1(sha1('SourceBans' . $password));
		
		return sha1((str_repeat(chr(0x5C), 64) ^ $key) . pack('H40', sha1((str_repeat(chr(0x36), 64) ^ $key) . $password)));
	}
	
	/**
	 * Returns a random password key
	 * 
	 * @return string a random password key
	 */
	public static function getPasswordKey()
	{
		return sprintf('%08x%08x%08x%08x', mt_rand(), mt_rand(), mt_rand(), mt_rand());
	}
}