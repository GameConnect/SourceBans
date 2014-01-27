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
			array('deny',  // deny anonymous users
				'actions'=>array('account'),
				'users'=>array('?'),
			),
			array('deny',
				'actions'=>array('protestban'),
				'expression'=>'!SourceBans::app()->settings->enable_protest',
			),
			array('deny',
				'actions'=>array('submitban'),
				'expression'=>'!SourceBans::app()->settings->enable_submit',
			),
			array('allow', // allow all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
		$defaultAction = 'action' . ucfirst(SourceBans::app()->settings->default_page);
		
		$this->$defaultAction();
	}

	/**
	 * Displays the dashboard page
	 */
	public function actionDashboard()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.dashboard.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.dashboard.title'),
		);
		
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
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.bans.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.bans.title'),
		);
		
		$hideInactive = Yii::app()->request->getQuery('hideinactive', 'false') == 'true';
		$search = Yii::app()->request->getQuery('q');
		
		$ban = new SBBan;
		
		$bans = new SBBan('search');
		$bans->unsetAttributes();  // clear any default values
		if(isset($_GET['SBBan']))
			$bans->attributes=$_GET['SBBan'];
		
		$comment = new SBComment;
		$comment->object_type = SBComment::TYPE_BAN;
		
		$this->render('bans', array(
			'ban' => $ban,
			'bans' => $bans,
			'comment' => $comment,
			'hideInactive' => $hideInactive,
			'search' => $search,
			'total_bans' => SBBan::model()->count(array(
				'scopes' => $hideInactive ? 'active' : null,
			)),
		));
	}

	/**
	 * Displays the servers page
	 */
	public function actionServers()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.servers.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.servers.title'),
		);
		
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
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.submitban.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.submitban.title'),
		);
		
		$model = new SBSubmission;
		$model->steam = 'STEAM_';
		
		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='submitban-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['SBSubmission']))
		{
			$model->attributes=$_POST['SBSubmission'];
			if($model->save())
			{
				SourceBans::log('Ban submission added', 'Ban against "' . $model->name . '" was submitted');
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->refresh();
			}
		}
		
		$games = SBGame::model()->with('servers:enabled')->findAll(array(
			'condition' => 'servers.id IS NOT NULL',
			'order' => 'name, servers.ip, servers.port',
		));
		
		$this->render('submitban', array(
			'model' => $model,
			'games' => $games,
		));
	}

	/**
	 * Displays the protest ban page
	 */
	public function actionProtestban()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.protestban.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.protestban.title'),
		);
		
		$model = new SBProtest;
		$model->ban_steam = 'STEAM_';
		$model->ban_ip = Yii::app()->request->userHostAddress;
		
		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='protestban-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['SBProtest']))
		{
			$model->attributes=$_POST['SBProtest'];
			if($model->save())
			{
				SourceBans::log('Ban protest added', 'Ban against "' . $model->user_email . '" was protested');
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->refresh();
			}
		}
		
		$this->render('protestban', array(
			'model' => $model,
		));
	}

	/**
	 * Displays the account page
	 */
	public function actionAccount()
	{
		$this->layout='//layouts/column2';
		
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.account.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.account.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'controllers.site.account.menu.permissions'), 'url'=>'#permissions'),
			array('label'=>Yii::t('sourcebans', 'controllers.site.account.menu.settings'), 'url'=>'#settings'),
			array('label'=>Yii::t('sourcebans', 'controllers.site.account.menu.email'), 'url'=>'#email'),
			array('label'=>Yii::t('sourcebans', 'controllers.site.account.menu.password'), 'url'=>'#password'),
			array('label'=>Yii::t('sourcebans', 'controllers.site.account.menu.server-password'), 'url'=>'#server-password'),
		);
		
		$model=new AccountForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && in_array($_POST['ajax'],array(
			'email-form',
			'password-form',
			'server-password-form',
			'settings-form',
		)))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['AccountForm']))
		{
			$model->setScenario($_POST['scenario']);
			$model->attributes=$_POST['AccountForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->save())
				$this->redirect(array('','#'=>'permissions'));
		}

		// display the account form
		$this->render('account', array(
			'model' => $model,
		));
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.error.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.error.title'),
		);
		
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
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.login.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.login.title'),
		);
		
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

	/**
	 * Displays the lost password page
	 */
	public function actionLostPassword()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.site.lostPassword.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.site.lostPassword.title'),
		);
		
		$model=new LostPasswordForm;

		// if it is ajax validation request
		if(isset($_POST['ajax']) && $_POST['ajax']==='lost-password-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		// collect user input data
		if(isset($_POST['LostPasswordForm']))
		{
			$model->attributes=$_POST['LostPasswordForm'];
			// validate user input and redirect to the previous page if valid
			if($model->validate() && $model->reset())
				$this->redirect(Yii::app()->user->returnUrl);
		}

		$email = Yii::app()->request->getQuery('email');
		$validationKey = Yii::app()->request->getQuery('key');
		if(!empty($email) && !empty($validationKey))
		{
			$admin = SBAdmin::model()->findByAttributes(array(
				'email' => $email,
				'validation_key' => $validationKey,
			));
			if($admin === null)
				throw new CHttpException(403, 'The validation key does not match the email address for this reset request.');
			
			$password = substr(str_shuffle('qwertyuiopasdfghjklmnbvcxz0987612345'), 0, 8);
			Yii::app()->mailer->AddAddress($admin->email);
			Yii::app()->mailer->Subject = Yii::t('sourcebans', 'controllers.site.lostPassword.subject');
			Yii::app()->mailer->MsgHtml(Yii::t('sourcebans', 'controllers.site.lostPassword.body', array(
				'{name}' => $admin->name,
				'{password}' => $password,
				'{link}' => CHtml::link(Yii::t('sourcebans', 'controllers.site.account.title'), array('site/account')),
			)));
			if(!Yii::app()->mailer->Send())
				throw new CHttpException(500, 'Please try again later or contact your system administrator.');
			
			$admin->new_password = $password;
			$admin->validation_key = null;
			$admin->save(false);
			$this->redirect(Yii::app()->user->homeUrl);
		}

		// display the lost password form
		$this->render('lostpassword',array('model'=>$model));
	}
}