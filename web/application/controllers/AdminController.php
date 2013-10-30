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
		
		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.index.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'controllers.admin.admins.title'), 'url'=>array('admin/admins'), 'itemOptions'=>array('class'=>'admins'), 'visible'=>Yii::app()->user->data->hasPermission('ADD_ADMINS', 'DELETE_ADMINS', 'EDIT_ADMINS', 'LIST_ADMINS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.bans.title'), 'url'=>array('admin/bans'), 'itemOptions'=>array('class'=>'bans'), 'visible'=>Yii::app()->user->data->hasPermission('ADD_BANS', 'IMPORT_BANS', 'BAN_PROTESTS', 'BAN_SUBMISSIONS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.groups.title'), 'url'=>array('admin/groups'), 'itemOptions'=>array('class'=>'groups'), 'visible'=>Yii::app()->user->data->hasPermission('ADD_GROUPS', 'DELETE_GROUPS', 'EDIT_GROUPS', 'LIST_GROUPS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.servers.title'), 'url'=>array('admin/servers'), 'itemOptions'=>array('class'=>'servers'), 'visible'=>Yii::app()->user->data->hasPermission('ADD_SERVERS', 'DELETE_SERVERS', 'EDIT_SERVERS', 'LIST_SERVERS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.games.title'), 'url'=>array('admin/games'), 'itemOptions'=>array('class'=>'games'), 'visible'=>Yii::app()->user->data->hasPermission('ADD_GAMES', 'DELETE_GAMES', 'EDIT_GAMES', 'LIST_GAMES')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.settings.title'), 'url'=>array('admin/settings'), 'itemOptions'=>array('class'=>'settings'), 'visible'=>Yii::app()->user->data->hasPermission('SETTINGS')),
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
		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.admins.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.admins.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'controllers.admin.admins.menu.list'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_ADMINS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.admins.menu.add'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_ADMINS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.admins.menu.import'), 'url'=>'#import', 'visible'=>Yii::app()->user->data->hasPermission('ADD_ADMINS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.admins.menu.overrides'), 'url'=>'#overrides', 'visible'=>Yii::app()->user->data->hasPermission('OVERRIDES')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.admins.menu.actions'), 'url'=>'#actions'),
		);
		
		$admin=new SBAdmin;
		
		$admins=new SBAdmin('search');
		$admins->unsetAttributes();  // clear any default values
		if(isset($_GET['SBAdmin']))
			$admins->attributes=$_GET['SBAdmin'];
		
		$actions=new SBAction('search');
		$actions->unsetAttributes();  // clear any default values
		if(isset($_GET['SBAction']))
			$actions->attributes=$_GET['SBAction'];
		
		$overrides=new SBOverride('search');
		$overrides->unsetAttributes();  // clear any default values
		if(isset($_GET['SBOverride']))
			$overrides->attributes=$_GET['SBOverride'];
		
		$this->render('admins',array(
			'actions'=>$actions,
			'admin'=>$admin,
			'admins'=>$admins,
			'overrides'=>$overrides,
		));
	}
	
	/**
	 * Displays the 'bans' admin page
	 */
	public function actionBans()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.bans.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.bans.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'controllers.admin.bans.menu.add'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_BANS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.bans.menu.import'), 'url'=>'#import', 'visible'=>Yii::app()->user->data->hasPermission('ADD_BANS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.bans.menu.protests'), 'url'=>'#protests', 'visible'=>Yii::app()->user->data->hasPermission('BAN_PROTESTS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.bans.menu.submissions'), 'url'=>'#submissions', 'visible'=>Yii::app()->user->data->hasPermission('BAN_SUBMISSIONS')),
		);
		
		$ban=new SBBan;
		$demo=new SBDemo;
		
		$protests=new SBProtest('search');
		$protests->unsetAttributes();  // clear any default values
		
		$submissions=new SBSubmission('search');
		$submissions->unsetAttributes();  // clear any default values
		
		$this->render('bans',array(
			'ban'=>$ban,
			'demo'=>$demo,
			'protests'=>$protests,
			'submissions'=>$submissions,
		));
	}
	
	/**
	 * Displays the 'games' admin page
	 */
	public function actionGames()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.games.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.games.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'controllers.admin.games.menu.list'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_GAMES')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.games.menu.add'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_GAMES')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.games.menu.map-image'), 'url'=>'#map-image', 'visible'=>Yii::app()->user->data->hasPermission('ADD_GAMES')),
		);
		
		$game=new SBGame;
		$map_image=new MapImageForm;
		
		$games=new SBGame('search');
		$games->unsetAttributes();  // clear any default values
		if(isset($_GET['SBGame']))
			$games->attributes=$_GET['SBGame'];
		
		$this->render('games',array(
			'game'=>$game,
			'games'=>$games,
			'map_image'=>$map_image,
		));
	}
	
	/**
	 * Displays the 'groups' admin page
	 */
	public function actionGroups()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.groups.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.groups.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'controllers.admin.groups.menu.list'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_GROUPS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.groups.menu.add'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_GROUPS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.groups.menu.import'), 'url'=>'#import', 'visible'=>Yii::app()->user->data->hasPermission('ADD_GROUPS')),
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
		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.servers.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.servers.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'controllers.admin.servers.menu.list'), 'url'=>'#list', 'visible'=>Yii::app()->user->data->hasPermission('LIST_SERVERS')),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.servers.menu.add'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_SERVERS')),
			array('label'=>Yii::t('sourcebans', 'controllers.servers.config.title'), 'url'=>array('servers/config'), 'visible'=>Yii::app()->user->data->hasFlag(SM_CONFIG)),
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
		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.settings.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.settings.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'controllers.admin.settings.menu.settings'), 'url'=>'#settings'),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.settings.menu.plugins'), 'url'=>'#plugins'),
			array('label'=>Yii::t('sourcebans', 'controllers.admin.settings.menu.logs'), 'url'=>'#logs'),
		);
		
		$model=new SettingsForm;
		
		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='settings-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['SettingsForm']))
		{
			$model->attributes=$_POST['SettingsForm'];
			if($model->validate() && $model->save())
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
			$pluginsList[$class]=NULL;
		}

		// Load already saved plugins
		$plugins=SBPlugin::model()->findAll();
		foreach($plugins as $plugin)
		{
			if(array_key_exists($plugin->class, $pluginsList))
			{
				# Plugin already saved in DB
				unset($pluginsList[$plugin->class]);
			}
		}
		$needReload=false;

		// Save new plugins into db
		foreach (array_keys($pluginsList) as $class) {
			$needReload=true;

			$plugin=new SBPlugin;
			$plugin->class=$class;
			$plugin->save();
		}

		if($needReload)
			$plugins=SBPlugin::model()->findAll();


		$logs=new SBLog('search');
		$logs->unsetAttributes();  // clear any default values
		if(isset($_GET['SBLog']))
			$logs->attributes=$_GET['SBLog'];
		
		$this->render('settings',array(
			'logs'=>$logs,
			'plugins'=>$plugins,
			'settings'=>$model,
		));
	}
	
	public function actionVersion()
	{
		$version = @file_get_contents('http://www.sourcebans.net/public/versionchecker/?type=rel');
		
		if(empty($version) || strlen($version) > 8)
		{
			Yii::app()->end(CJSON::encode(array(
				'error' => Yii::t('sourcebans', 'controllers.admin.version.error'),
			)));
		}
		
		Yii::app()->end(CJSON::encode(array(
			'version' => $version,
			'update'  => version_compare($version, SourceBans::getVersion()) > 0,
		)));
	}
}