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
 * @property integer $group_id Web group ID
 * @property string $email Email address
 * @property string $language Language
 * @property string $theme Theme
 * @property string $timezone Timezone
 * @property string $server_password Server password
 * @property string $validation_key Validation key
 * @property integer $login_time Last login time
 * @property integer $create_time Date/Time
 * @property SteamProfile $community Steam Community data
 * @property integer $communityId Steam Community ID
 * @property string $flags Server permissions
 * @property integer $immunity Immunity level
 *
 * The followings are the available model relations:
 * @property SBAction[] $actions Actions
 * @property SBBan[] $bans Bans
 * @property SBComment[] $comments Comments
 * @property SBGroup $group Web group
 * @property SBLog[] $logs Logs
 * @property SBServerGroup[] $server_groups Server groups
 * @property SBBan[] $unbanned_bans Unbanned bans
 * @property SBComment[] $updated_comments Edited comments
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBAdmin extends CActiveRecord
{
	const AUTH_IP    = 'ip';
	const AUTH_NAME  = 'name';
	const AUTH_STEAM = 'steam';
	
	public $new_password;
	
	/**
	 * @var integer Steam Community ID
	 */
	protected $admin_community_id;
	
	private $_community;
	private $_flags;
	private $_immunity;
	
	
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

	public function init()
	{
		$this->attachEventHandler('onAfterDelete', array($this, '_onAfterDelete'));
		$this->attachEventHandler('onAfterSave',   array($this, '_onAfterSave'));
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
			array('identity', 'application.validators.SBAdminIdentityValidator'),
			array('new_password, server_password', 'length', 'max'=>64, 'min'=>SourceBans::app()->settings->password_min_length),
			array('email', 'email'),
			array('email', 'length', 'max'=>128),
			array('language, theme, timezone, server_password', 'default', 'setOnEmpty'=>true),
			array('password, validation_key, login_time, server_groups', 'safe'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, auth, identity, group_id, email, language, theme, login_time, create_time', 'safe', 'on'=>'search'),
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
			'bans' => array(self::HAS_MANY, 'SBBan', 'admin_id'),
			'comments' => array(self::HAS_MANY, 'SBComment', 'admin_id'),
			'group' => array(self::BELONGS_TO, 'SBGroup', 'group_id'),
			'logs' => array(self::HAS_MANY, 'SBLog', 'admin_id'),
			'server_groups' => array(self::MANY_MANY, 'SBServerGroup', '{{admins_server_groups}}(admin_id, group_id)', 'order' => 'inherit_order'),
			'unbanned_bans' => array(self::HAS_MANY, 'SBBan', 'unban_admin_id'),
			'updated_comments' => array(self::HAS_MANY, 'SBComment', 'update_admin_id'),
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
			'timezone' => Yii::t('sourcebans', 'Timezone'),
			'server_password' => Yii::t('sourcebans', 'Server password'),
			'login_time' => Yii::t('sourcebans', 'Last visit'),
			'create_time' => Yii::t('sourcebans', 'Date') . '/' . Yii::t('sourcebans', 'Time'),
			'new_password' => Yii::t('sourcebans', 'Password'),
			'communityId' => Yii::t('sourcebans', 'Steam Community ID'),
			'flags' => Yii::t('sourcebans', 'Server permissions'),
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

		$criteria->compare('t.id', $this->id);
		$criteria->compare('t.name', $this->name, true);
		$criteria->compare('t.auth', $this->auth, true);
		$criteria->compare('t.identity', $this->identity, true);
		$criteria->compare('t.group_id', $this->group_id);
		$criteria->compare('t.email', $this->email, true);
		$criteria->compare('t.language', $this->language, true);
		$criteria->compare('t.theme', $this->theme, true);
		$criteria->compare('t.login_time', $this->login_time);
		$criteria->compare('t.create_time', $this->create_time);

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
			'CTimestampBehavior'=>array(
				'class'=>'zii.behaviors.CTimestampBehavior',
				'updateAttribute'=>null,
			),
		);
	}
	
	/**
	 * Returns the Steam Community data
	 * 
	 * @return SteamProfile the Steam Community data
	 */
	public function getCommunity()
	{
		if(empty($this->communityId))
			return null;
		
		if(!isset($this->_community))
		{
			$this->_community = new SteamProfile($this->communityId);
		}
		
		return $this->_community;
	}
	
	/**
	 * Returns the Steam Community ID
	 * 
	 * @return integer the Steam Community ID
	 */
	public function getCommunityId()
	{
		return $this->admin_community_id;
	}
	
	/**
	 * Returns the server permissions
	 * 
	 * @return string the server permissions
	 */
	public function getFlags()
	{
		if(!isset($this->_flags))
		{
			foreach($this->server_groups as $server_group)
			{
				$this->_flags .= $server_group->flags;
			}
			$this->_flags = count_chars($this->_flags, 3);
		}
		
		return $this->_flags;
	}
	
	/**
	 * Returns the immunity level
	 * 
	 * @return integer the immunity level
	 */
	public function getImmunity()
	{
		if(!isset($this->_immunity))
		{
			$this->_immunity = 0;
			foreach($this->server_groups as $server_group)
			{
				if($server_group->immunity > $this->_immunity)
					$this->_immunity = $server_group->immunity;
			}
		}
		
		return $this->_immunity;
	}
	
	/**
	 * Returns whether the admin has one of these server permissions
	 * 
	 * @param mixed $flag flag(s) to check for
	 * @return boolean whether the admin has one of these server permissions
	 */
	public function hasFlag($flag)
	{
		if(func_num_args() > 1)
			$flags = func_get_args();
		else if(is_array($flag))
			$flags = $flag;
		else
			$flags = str_split($flag);
		
		if(strpos($this->getFlags(), SM_ROOT) !== false)
			return true;
		
		foreach($flags as $flag)
		{
			if(strpos($this->getFlags(), $flag) !== false)
				return true;
		}
		
		return false;
	}
	
	/**
	 * Returns whether the admin has all of these server permissions
	 * 
	 * @param mixed $flag flag(s) to check for
	 * @return boolean whether the admin has all of these server permissions
	 */
	public function hasFlags($flag)
	{
		if(func_num_args() > 1)
			$flags = func_get_args();
		else if(is_array($flag))
			$flags = $flag;
		else
			$flags = str_split($flag);
		
		if(strpos($this->getFlags(), SM_ROOT) !== false)
			return true;
		
		foreach($flags as $flag)
		{
			if(strpos($this->getFlags(), $flag) === false)
				return false;
		}
		
		return true;
	}
	
	/**
	 * Returns whether the admin has one of these server groups
	 * 
	 * @param mixed $name group name(s) to check for
	 * @return boolean whether the admin has one of these server groups
	 */
	public function hasGroup($name)
	{
		$names  = is_array($name) ? $name : func_get_args();
		$groups = array_intersect($this->server_groups, $names);
		
		return !empty($groups);
	}
	
	/**
	 * Returns whether the admin has one of these web permissions
	 * 
	 * @param mixed $name permission name(s) to check for
	 * @return boolean whether the admin has one of these web permissions
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
	 * @param mixed $name permission name(s) to check for
	 * @return boolean whether the admin has all of these web permissions
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
		$this->password = CPasswordHelper::hashPassword($password);
	}
	
	public function validatePassword($password)
	{
		// Backwards compatibility with old password format
		if(strlen($this->password) == 40)
			return $this->password == sha1(sha1('SourceBans' . $password));
		
		return CPasswordHelper::verifyPassword($password, $this->password);
	}
	
	
	protected function _onAfterDelete($event)
	{
		SourceBans::app()->trigger('admins.deleteAdmin', $event);
	}
	
	protected function _onAfterSave($event)
	{
		SourceBans::app()->trigger('admins.saveAdmin', $event);
	}
	
	
	/**
	 * Returns the supported authentication types
	 * 
	 * @return array the supported authentication types
	 */
	public static function getAuthTypes()
	{
		return array(
			self::AUTH_STEAM => Yii::t('sourcebans', 'Steam ID'),
			self::AUTH_IP    => Yii::t('sourcebans', 'IP address'),
			self::AUTH_NAME  => Yii::t('sourcebans', 'Name'),
		);
	}
	
	
	protected function afterSave()
	{
		if($this->server_groups !== null)
		{
			// Delete old server groups
			$criteria = new CDbCriteria();
			$criteria->condition = 'admin_id = :admin_id';
			$criteria->params = array(':admin_id' => $this->id);
			$this->commandBuilder->createDeleteCommand('{{admins_server_groups}}', $criteria)->execute();
			
			if($this->server_groups !== array())
			{
				// Insert new server groups, order by inherit_order
				$i = 0;
				$server_groups = array();
				foreach((array)$this->server_groups as $group)
				{
					$server_groups[] = array(
						'admin_id' => $this->id,
						'group_id' => $group instanceof SBServerGroup ? $group->id : $group,
						'inherit_order' => ++$i,
					);
				}
				$this->commandBuilder->createMultipleInsertCommand('{{admins_server_groups}}', $server_groups)->execute();
			}
		}
		
		parent::afterSave();
	}
	
	protected function beforeFind()
	{
		$t = $this->tableAlias;
		
		// Select community ID
		$select=array(
			'(CASE '.$t.'.auth WHEN "'.self::AUTH_STEAM.'" THEN 76561197960265728 + CAST(MID('.$t.'.identity, 9, 1) AS UNSIGNED) + CAST(MID('.$t.'.identity, 11, 10) * 2 AS UNSIGNED) END) AS admin_community_id',
		);
		if($this->dbCriteria->select==='*')
		{
			array_unshift($select,'*');
		}
		$this->dbCriteria->mergeWith(array(
			'select'=>$select,
		));
		
		parent::beforeFind();
	}
	
	protected function beforeSave()
	{
		if(($this->new_password = trim($this->new_password)) != '')
		{
			$this->setPassword($this->new_password);
		}
		if($this->auth == self::AUTH_STEAM)
		{
			$this->identity = strtoupper($this->identity);
		}
		
		return parent::beforeSave();
	}
}