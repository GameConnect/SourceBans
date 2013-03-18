<?php
/**
 * SourceBans admin controller
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.controllers
 * @since 2.0
 */
class AdminController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout.
	 */
	public $layout='//layouts/column2';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}
	
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',
				'actions'=>array('admins'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_ADMINS", "DELETE_ADMINS", "EDIT_ADMINS", "LIST_ADMINS")',
			),
			array('allow',
				'actions'=>array('bans'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_BANS", "IMPORT_BANS", "BAN_PROTESTS", "BAN_SUBMISSIONS")',
			),
			array('allow',
				'actions'=>array('games'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_GAMES", "DELETE_GAMES", "EDIT_GAMES", "LIST_GAMES")',
			),
			array('allow',
				'actions'=>array('groups'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_GROUPS", "DELETE_GROUPS", "EDIT_GROUPS", "LIST_GROUPS")',
			),
			array('allow',
				'actions'=>array('servers'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_SERVERS", "DELETE_SERVERS", "EDIT_SERVERS", "LIST_SERVERS")',
			),
			array('allow',
				'actions'=>array('settings'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("SETTINGS")',
			),
			array('allow',
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	/**
	 * Displays the admin page
	 */
	public function actionIndex()
	{
		$this->layout='//layouts/column1';
		
		$this->pageTitle=Yii::t('sourcebans', 'Administration');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration'),
		);
		
		$demosize=Helpers::getDirectorySize(Yii::getPathOfAlias('webroot.demos'));
		
		$this->render('index', array(
			'demosize' => Yii::app()->format->formatSize($demosize['size']),
			'total_admins' => SBAdmin::model()->count(),
			'total_archived_protests' => SBProtest::model()->countByAttributes(array('archived' => true)),
			'total_archived_submissions' => SBSubmission::model()->countByAttributes(array('archived' => true)),
			'total_bans' => SBBan::model()->count(),
			'total_blocks' => SBBlock::model()->count(),
			'total_protests' => SBProtest::model()->countByAttributes(array('archived' => false)),
			'total_servers' => SBServer::model()->count(),
			'total_submissions' => SBSubmission::model()->countByAttributes(array('archived' => false)),
		));
	}
	
	/**
	 * Displays the 'admins' admin page
	 */
	public function actionAdmins()
	{
		$this->pageTitle=Yii::t('sourcebans', 'Admins');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Admins'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'List admins'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_ADMINS')),
			array('label'=>Yii::t('sourcebans', 'Add admin'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_ADMINS')),
		);
		
		$admin=new SBAdmin;
		
		$admins=new SBAdmin('search');
		$admins->unsetAttributes();  // clear any default values
		if(isset($_GET['SBAdmin']))
			$admins->attributes=$_GET['SBAdmin'];
		
		$this->render('admins',array(
			'admin'=>$admin,
			'admins'=>$admins,
		));
	}
	
	/**
	 * Displays the 'bans' admin page
	 */
	public function actionBans()
	{
		$this->pageTitle=Yii::t('sourcebans', 'Bans');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Bans'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Add ban'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_BANS')),
		);
		
		$ban=new SBBan;
		
		$this->render('bans',array(
			'ban'=>$ban,
		));
	}
	
	/**
	 * Displays the 'games' admin page
	 */
	public function actionGames()
	{
		$this->pageTitle=Yii::t('sourcebans', 'Games');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Games'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'List games'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_GAMES')),
			array('label'=>Yii::t('sourcebans', 'Add game'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_GAMES')),
		);
		
		$game=new SBGame;
		
		$games=new SBGame('search');
		$games->unsetAttributes();  // clear any default values
		if(isset($_GET['SBGame']))
			$games->attributes=$_GET['SBGame'];
		
		$this->render('games',array(
			'game'=>$game,
			'games'=>$games,
		));
	}
	
	/**
	 * Displays the 'groups' admin page
	 */
	public function actionGroups()
	{
		$this->pageTitle=Yii::t('sourcebans', 'Groups');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Groups'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'List groups'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_GROUPS')),
			array('label'=>Yii::t('sourcebans', 'Add group'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_GROUPS')),
		);
		
		$group=new SBGroup;
		$server_group=new SBServerGroup;
		
		$groups=new SBGroup('search');
		$groups->unsetAttributes();  // clear any default values
		
		$server_groups=new SBServerGroup('search');
		$server_groups->unsetAttributes();  // clear any default values
		
		$this->render('groups',array(
			'group'=>$group,
			'groups'=>$groups,
			'server_group'=>$server_group,
			'server_groups'=>$server_groups,
		));
	}
	
	/**
	 * Displays the 'servers' admin page
	 */
	public function actionServers()
	{
		$this->pageTitle=Yii::t('sourcebans', 'Servers');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Servers'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'List servers'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_SERVERS')),
			array('label'=>Yii::t('sourcebans', 'Add server'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_SERVERS')),
		);
		
		$server=new SBServer;
		
		$servers=new SBServer('search');
		$servers->unsetAttributes();  // clear any default values
		if(isset($_GET['SBServer']))
			$servers->attributes=$_GET['SBServer'];
		
		$this->render('servers',array(
			'server'=>$server,
			'servers'=>$servers,
		));
	}
	
	/**
	 * Displays the 'settings' admin page
	 */
	public function actionSettings()
	{
		$this->pageTitle=Yii::t('sourcebans', 'Settings');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Settings'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Settings'), 'url'=>'#settings'),
			array('label'=>Yii::t('sourcebans', 'Plugins'), 'url'=>'#plugins'),
		);
		
		if(isset($_POST['settings']))
		{
			$settings = SBSetting::model()->findAll(array('index' => 'name'));
			
			foreach($_POST['settings'] as $name => $value)
			{
				$settings[$name]->value = $value;
				$settings[$name]->save();
			}
			
			$this->redirect(array('','#'=>'settings'));
		}
		
		// Find new plugins and save to database
		$files=CFileHelper::findFiles(Yii::getPathOfAlias('application.plugins'), array(
			'fileTypes'=>array('php'),
			'level'=>1,
		));
		foreach($files as $file)
		{
			$id=substr(pathinfo($file, PATHINFO_DIRNAME), strlen(Yii::getPathOfAlias('application.plugins')) + 1);
			$class=(!empty($id)?$id.'.':'').pathinfo($file, PATHINFO_FILENAME);
			
			$plugin=new SBPlugin;
			$plugin->class=$class;
			
			try
			{
				$plugin->save();
			}
			catch(CDbException $e)
			{
				// Ignore duplicate keys
				if($e->errorInfo[1] != 1062)
					throw $e;
			}
		}
		
		$plugins=SBPlugin::model()->findAll();
		
		$this->render('settings',array(
			'plugins'=>$plugins,
		));
	}
	
	public function actionVersion()
	{
		$version = @file_get_contents('http://www.sourcebans.net/public/versionchecker/?type=rel');
		
		if(empty($version) || strlen($version) > 8)
		{
			Yii::app()->end(CJSON::encode(array(
				'error' => Yii::t('sourcebans', 'Error retrieving latest release.'),
			)));
		}
		
		Yii::app()->end(CJSON::encode(array(
			'version' => $version,
			'update'  => version_compare($version, SourceBans::getVersion()) > 0,
		)));
	}
}