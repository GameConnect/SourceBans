<?php

/**
 * This is the model class for table "{{admins}}".
 *
 * The followings are the available columns in table '{{admins}}':
 * @property integer $id
 * @property string $name
 * @property string $auth
 * @property string $identity
 * @property string $password
 * @property string $password_key
 * @property integer $group_id
 * @property string $email
 * @property string $language
 * @property string $theme
 * @property string $srv_password
 * @property string $validate
 * @property string $lastvisit
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
 */
class SBAdmin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Admins the static model class
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
			array('name, identity, password, srv_password, validate', 'length', 'max'=>64),
			array('auth', 'length', 'max'=>5),
			array('email', 'length', 'max'=>128),
			array('language', 'length', 'max'=>2),
			array('theme', 'length', 'max'=>32),
			array('lastvisit', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, auth, identity, password, group_id, email, language, theme, srv_password, validate, lastvisit', 'safe', 'on'=>'search'),
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
			'group_id' => Yii::t('sourcebans', 'Group'),
			'email' => Yii::t('sourcebans', 'Email address'),
			'language' => Yii::t('sourcebans', 'Language'),
			'theme' => Yii::t('sourcebans', 'Theme'),
			'srv_password' => Yii::t('sourcebans', 'Server password'),
			'validate' => 'Validate',
			'lastvisit' => Yii::t('sourcebans', 'Last visit'),
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
		$criteria->compare('auth',$this->auth,true);
		$criteria->compare('identity',$this->identity,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('group_id',$this->group_id);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('language',$this->language,true);
		$criteria->compare('theme',$this->theme,true);
		$criteria->compare('srv_password',$this->srv_password,true);
		$criteria->compare('validate',$this->validate,true);
		$criteria->compare('lastvisit',$this->lastvisit,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	
	
	/**
	 * Check whether admin has one of these permissions
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
	 * Check whether admin has all of these permissions
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
	
	
	public static function getPasswordHash($password, $key)
	{
		if(empty($password) || empty($key))
			return null;
		
		return sha1((str_repeat(chr(0x5C), 64) ^ $key) . pack('H40', sha1((str_repeat(chr(0x36), 64) ^ $key) . $password)));
	}
	
	public static function getPasswordKey()
	{
		return sprintf('%08x%08x%08x%08x', mt_rand(), mt_rand(), mt_rand(), mt_rand());
	}
}