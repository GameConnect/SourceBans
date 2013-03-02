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
			'postOnly + delete', // we only allow deletion via POST request
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
				'users' => array('@'),
			),
			array('deny',
				'users' => array('*'),
			),
		);
	}
	
	/**
	 * Displays the admin page
	 */
	public function actionIndex()
	{
	  $this->layout = '//layouts/column1';
		$this->pageTitle = Yii::t('sourcebans', 'Administration');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Administration'),
		);
		
		$demosize = Helpers::getDirectorySize(Yii::getPathOfAlias('webroot.demos'));
		
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
		$this->pageTitle = Yii::t('sourcebans', 'Admins');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Admins'),
		);
		$this->menu = array(
			array('label'=>Yii::t('sourcebans', 'List admins'), 'url'=>'#list'),
			array('label'=>Yii::t('sourcebans', 'Add admin'), 'url'=>'#add'),
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
		$this->pageTitle = Yii::t('sourcebans', 'Bans');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Bans'),
		);
		$this->menu = array(
			array('label'=>Yii::t('sourcebans', 'Add ban'), 'url'=>'#add'),
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
		$this->pageTitle = Yii::t('sourcebans', 'Games');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Games'),
		);
		$this->menu = array(
			array('label'=>Yii::t('sourcebans', 'List games'), 'url'=>'#list'),
			array('label'=>Yii::t('sourcebans', 'Add game'), 'url'=>'#add'),
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
		$this->pageTitle = Yii::t('sourcebans', 'Groups');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Groups'),
		);
		$this->menu = array(
			array('label'=>Yii::t('sourcebans', 'List groups'), 'url'=>'#list'),
			array('label'=>Yii::t('sourcebans', 'Add group'), 'url'=>'#add'),
		);
		
		$group=new SBServerGroup;
		
		$groups=new SBServerGroup('search');
		$groups->unsetAttributes();  // clear any default values
		if(isset($_GET['SBServerGroup']))
			$groups->attributes=$_GET['SBServerGroup'];
		
		$this->render('groups',array(
			'group'=>$group,
			'groups'=>$groups,
		));
	}
	
	/**
	 * Displays the 'servers' admin page
	 */
	public function actionServers()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Servers');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Servers'),
		);
		$this->menu = array(
			array('label'=>Yii::t('sourcebans', 'List servers'), 'url'=>'#list'),
			array('label'=>Yii::t('sourcebans', 'Add server'), 'url'=>'#add'),
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
		$this->pageTitle = Yii::t('sourcebans', 'Settings');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Settings'),
		);
		$this->menu = array(
			array('label'=>Yii::t('sourcebans', 'Plugins'), 'url'=>'#plugins'),
		);
		
		// Find new plugins and save to database
		$files=CFileHelper::findFiles(Yii::getPathOfAlias('application.plugins'), array(
			'fileTypes' => array('php'),
			'level' => 0,
		));
		foreach($files as $file)
		{
			$plugin=new SBPlugin;
			$plugin->class=pathinfo($file, PATHINFO_FILENAME);
			
			try
			{
				$plugin->save();
			}
			catch(CDbException $e)
			{
				// Ignore duplicate keys
				if ($e->errorInfo[1] != 1062)
					throw $e;
			}
		}
		
		$plugins=SBPlugin::model()->findAll();
		
		$this->render('settings',array(
			'plugins'=>$plugins,
		));
	}
}