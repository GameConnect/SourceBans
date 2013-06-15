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
 * @property integer $status Status
 * @property string $id ID
 * @property string $name Name
 * @property string $description Description
 * @property string $author Author
 * @property string $version Version
 * @property string $url URL
 * @property boolean $isEnabled Whether the plugin is enabled
 * @property boolean $isInstalled Whether the plugin is installed
 * @property string $path Path to the plugin directory
 * @property string $pathAlias Path alias to the plugin directory
 *
 * @package sourcebans.models
 * @since 2.0
 */
class SBPlugin extends CActiveRecord
{
	const STATUS_INSTALLED = 1;
	const STATUS_ENABLED   = 2;
	
	/**
	 * @var boolean whether the plugin can be disabled.
	 * You may set this to false when the plugin does not provide any web functionality,
	 * for example if it simply provides a page to configure a game plugins' settings.
	 * This property will be ignored if the plugin does not have an installation procedure.
	 * @see canDisable()
	 */
	public $allowDisable = true;
	
	private static $_plugins;
	
	
	public function __toString()
	{
		return $this->name;
	}
	
	
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
			array('status', 'numerical', 'integerOnly'=>true),
			array('class', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('class, status', 'safe', 'on'=>'search'),
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
			'status' => Yii::t('sourcebans', 'Status'),
			'name' => Yii::t('sourcebans', 'Name'),
			'description' => Yii::t('sourcebans', 'Description'),
			'author' => Yii::t('sourcebans', 'Author'),
			'version' => Yii::t('sourcebans', 'Version'),
			'url' => Yii::t('sourcebans', 'URL'),
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

		$criteria->compare('t.class',$this->class,true);
		$criteria->compare('t.status',$this->status);

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
				'condition'=>$t.'.status != '.self::STATUS_ENABLED,
			),
			'enabled'=>array(
				'condition'=>$t.'.status = '.self::STATUS_ENABLED,
			),
		);
	}
	
	/**
	 * Returns whether the plugin can be disabled.
	 * 
	 * @return boolean whether the plugin can be disabled
	 */
	public function canDisable()
	{
		if($this->allowDisable)
			return true;
		
		return !$this->hasInstall();
	}

	public function findById($id)
	{
		static $_plugins;
		if(!isset($_plugins))
		{
			$_plugins = SBPlugin::model()->findAll(array('index' => 'id'));
		}
		
		return $_plugins[$id];
	}
	
	public function getAction()
	{
		if($this->isEnabled)
			return $this->canDisable() ? 'Disable' : 'Uninstall';
		
		if($this->isInstalled)
			return $this->canDisable() ? 'Enable' : 'Uninstall';
		
		return $this->hasInstall() ? 'Install' : 'Enable';
	}
	
	public function getId()
	{
		return strtok($this->class, '.');
	}
	
	/**
	 * Returns whether the plugin is enabled
	 * 
	 * @return boolean whether the plugin is enabled
	 */
	public function getIsEnabled()
	{
		return $this->status == self::STATUS_ENABLED;
	}
	
	/**
	 * Returns whether the plugin is installed
	 * 
	 * @return boolean whether the plugin is installed
	 */
	public function getIsInstalled()
	{
		return $this->status == self::STATUS_INSTALLED;
	}
	
	/**
	 * Returns a path relative to the plugin directory
	 * 
	 * @param string $alias alias (e.g. models.SBPlugin)
	 * @return mixed file path corresponding to the alias, false if the alias is invalid.
	 */
	public function getPath($alias = null)
	{
		return Yii::getPathOfAlias($this->getPathAlias($alias));
	}
	
	/**
	 * Returns a path alias for the plugin
	 * 
	 * @param string $alias alias (e.g. models.SBPlugin)
	 * @return string a path alias for the plugin
	 */
	public function getPathAlias($alias = null)
	{
		return 'application.plugins.' . $this->id . ($alias == '' ? '' : '.' . $alias);
	}
	
	public function getViewFile($viewName)
	{
		$viewName = $this->getPathAlias('views.' . $viewName);
		if(Yii::app()->controller->getViewFile($viewName))
			return $viewName;
		
		return null;
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
	 * Returns whether the plugin has an installation procedure
	 * 
	 * @return boolean whether the plugin has an installation procedure
	 */
	public function hasInstall()
	{
		$class  = new ReflectionClass($this);
		$method = $class->getMethod('runInstall');
		
		return $class->getName() == $method->getDeclaringClass()->getName();
	}
	
	/**
	 * Returns whether the plugin has an uninstallation procedure
	 * 
	 * @return boolean whether the plugin has an uninstallation procedure
	 */
	public function hasUninstall()
	{
		$class  = new ReflectionClass($this);
		$method = $class->getMethod('runUninstall');
		
		return $class->getName() == $method->getDeclaringClass()->getName();
	}
	
	/**
	 * This method is invoked when installing the plugin.
	 * 
	 * @return boolean Whether the installation was successful
	 */
	public function runInstall()
	{
		return true;
	}
	
	/**
	 * This method is invoked right before the settings action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 */
	public function runSettings() {}
	
	/**
	 * This method is invoked when uninstalling the plugin.
	 * 
	 * @return boolean Whether the uninstallation was successful
	 */
	public function runUninstall()
	{
		return true;
	}
	
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
	
	public function onAddAdmin(SBAdmin $admin) {}
	public function onAddBan(SBBan $ban) {}
	public function onAddGame(SBGame $game) {}
	public function onAddServer(SBServer $server) {}
	public function onAddServerGroup(SBServerGroup $group) {}
	public function onAddWebGroup(SBGroup $group) {}
	
	public function onDeleteAdmin(SBAdmin $admin) {}
	public function onDeleteBan(SBBan $ban) {}
	public function onDeleteGame(SBGame $game) {}
	public function onDeleteServer(SBServer $server) {}
	public function onDeleteServerGroup(SBServerGroup $group) {}
	public function onDeleteWebGroup(SBGroup $group) {}
	
	public function onEditAdmin(SBAdmin $admin) {}
	public function onEditBan(SBBan $ban) {}
	public function onEditGame(SBGame $game) {}
	public function onEditServer(SBServer $server) {}
	public function onEditServerGroup(SBServerGroup $group) {}
	public function onEditWebGroup(SBGroup $group) {}
	
	
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
		
		$id = strtok($attributes['class'], '.');
		if(!isset(self::$_plugins[$id]))
			self::$_plugins[$id]=new $class(null);
		
		return self::$_plugins[$id];
	}
}