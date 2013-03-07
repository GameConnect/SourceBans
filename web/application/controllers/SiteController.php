<?php
/**
 * SourceBans site controller
 * 
 * @author GameConnect
 * @copyright (C)2007-2013 GameConnect.net.  All rights reserved.
 * @link http://www.sourcebans.net
 * 
 * @package sourcebans.controllers
 * @since 2.0
 */
class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'application/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$defaultAction = "action" . ucfirst(SourceBans::app()->settings->default_page);
		
		$this->$defaultAction();
	}

	/**
	 * Displays the dashboard page
	 */
	public function actionDashboard()
	{
		$bans = SBBan::model()->search();
		$bans->criteria->limit = 10;
		$bans->criteria->with = array('server', 'server.game');
		$bans->pagination = false;
		
		$blocks = SBBlock::model()->search();
		$blocks->criteria->limit = 10;
		$blocks->criteria->with = 'ban';
		$blocks->pagination = false;
		
		$servers = SBServer::model()->search();
		$servers->criteria->scopes = 'enabled';
		$servers->pagination = false;
		
		$this->render('dashboard', array(
			'bans' => $bans,
			'blocks' => $blocks,
			'servers' => $servers,
			'total_bans' => SBBan::model()->count(),
			'total_blocks' => SBBlock::model()->count(),
		));
	}

	/**
	 * Displays the bans page
	 */
	public function actionBans()
	{
		$hideInactive = Yii::app()->request->getQuery('hideinactive', 'false') == 'true';
		$search = Yii::app()->request->getQuery('q');
		
		$bans = new SBBan('search');
		$bans->unsetAttributes();  // clear any default values
		if(isset($_GET['SBBan']))
			$bans->attributes=$_GET['SBBan'];
		
		$this->render('bans', array(
			'bans' => $bans,
			'hideInactive' => $hideInactive,
			'search' => $search,
			'total_bans' => SBBan::model()->count(),
		));
	}

	/**
	 * Displays the servers page
	 */
	public function actionServers()
	{
		$servers = SBServer::model()->search();
		$servers->criteria->scopes = 'enabled';
		$servers->pagination = false;
		
		$this->render('servers', array(
			'servers' => $servers,
		));
	}

	/**
	 * Displays the submit ban page
	 */
	public function actionSubmitban()
	{
		$model = new SBSubmission;
		$model->demo = new SBDemo;
		
		$servers = SBServer::model()->enabled()->findAll(array(
			'order' => 'ip, port',
		));
		
		$this->render('submitban', array(
			'model' => $model,
			'servers' => $servers,
		));
	}

	/**
	 * Displays the protest ban page
	 */
	public function actionProtestban()
	{
		$model = new SBProtest;
		$model->ban = new SBBan;
		
		$this->render('protestban', array(
			'model' => $model,
		));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model=new LoginForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LoginForm']))
		{
			$model->attributes=$_POST['LoginForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->login())
				$this->redirect(Yii::app()->user->returnUrl);
		}
		// display the login form
		$this->render('login',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
}