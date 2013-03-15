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
}