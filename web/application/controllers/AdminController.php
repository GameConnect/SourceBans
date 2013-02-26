<?php

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
	
	public function actionAdmins()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Admins') . Yii::app()->params['titleSeparator'] . Yii::t('sourcebans', 'Administration');
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
	
	public function actionBans()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Bans') . Yii::app()->params['titleSeparator'] . Yii::t('sourcebans', 'Administration');
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
	
	public function actionGames()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Games') . Yii::app()->params['titleSeparator'] . Yii::t('sourcebans', 'Administration');
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
	
	public function actionGroups()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Groups') . Yii::app()->params['titleSeparator'] . Yii::t('sourcebans', 'Administration');
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
	
	public function actionServers()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Servers') . Yii::app()->params['titleSeparator'] . Yii::t('sourcebans', 'Administration');
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
}