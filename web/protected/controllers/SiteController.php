<?php

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
			// page action renders "static" pages stored under 'protected/views/site/pages'
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

	public function actionDashboard()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Dashboard');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Dashboard'),
		);
		
		$bans = new CActiveDataProvider('SBBan', array(
			'criteria' => array(
				'limit' => 10,
				'with' => array('server', 'server.game'),
			),
			'sort' => array(
				'defaultOrder' => array(
					'time' => CSort::SORT_DESC,
				),
			),
		));
		$blocks = new CActiveDataProvider('SBBlock', array(
			'criteria' => array(
				'limit' => 10,
				'with' => array('ban'),
			),
			'sort' => array(
				'defaultOrder' => array(
					'time' => CSort::SORT_DESC,
				),
			),
		));
		$servers = new CActiveDataProvider('SBServer', array(
			'criteria' => array(
				'condition' => 't.enabled = 1',
				'with' => array('game'),
			),
			'pagination' => false,
			'sort' => array(
				'attributes' => array(
					'game.name' => array(
						'asc' => 'game.name',
						'desc' => 'game.name DESC',
					),
					'*',
				),
				'defaultOrder' => array(
					'game.name' => CSort::SORT_ASC,
					'ip' => CSort::SORT_ASC,
					'port' => CSort::SORT_ASC,
				),
			),
		));
		
		$this->render('dashboard', array(
			'bans' => $bans,
			'blocks' => $blocks,
			'servers' => $servers,
			'total_bans' => SBBan::model()->count(),
			'total_blocks' => SBBlock::model()->count(),
		));
	}

	public function actionBans()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Bans');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Bans'),
		);
		
		$hideInactive = Yii::app()->request->getQuery('hideinactive', 'false') == 'true';

		$bans = new CActiveDataProvider('SBBan', array(
			'criteria' => array(
		    'scopes' => $hideInactive ? 'active' : null,
				'with' => array('admin', 'country', 'server', 'server.game'),
			),
			'pagination' => array(
				'pageSize' => SourceBans::app()->settings->items_per_page,
			),
			'sort' => array(
				'attributes' => array(
					'admin.name' => array(
						'asc' => 'admin.name',
						'desc' => 'admin.name DESC',
					),
					'*',
				),
				'defaultOrder' => array(
					'time' => CSort::SORT_DESC,
				),
			),
		));
		
		$this->render('bans', array(
			'bans' => $bans,
	    'hideInactive' => $hideInactive,
			'total_bans' => SBBan::model()->count(),
		));
	}

	public function actionServers()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Servers');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Servers'),
		);
		
		$servers = new CActiveDataProvider('SBServer', array(
			'criteria' => array(
				'condition' => 't.enabled = 1',
				'with' => array('game'),
			),
			'pagination' => false,
			'sort' => array(
				'attributes' => array(
					'game.name' => array(
						'asc' => 'game.name',
						'desc' => 'game.name DESC',
					),
					'*',
				),
				'defaultOrder' => array(
					'game.name' => CSort::SORT_ASC,
					'ip' => CSort::SORT_ASC,
					'port' => CSort::SORT_ASC,
				),
			),
		));
		
		$this->render('servers', array(
			'servers' => $servers,
		));
	}

	public function actionSubmitban()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Submit ban');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Submit ban'),
		);
		
		$model = new SBSubmission;
		$model->demo = new SBDemo;
		
		$servers = SBServer::model()->findAll(array(
			'order' => 'ip, port',
		));
		
		$this->render('submitban', array(
			'model' => $model,
			'servers' => $servers,
		));
	}

	public function actionProtestban()
	{
		$this->pageTitle = Yii::t('sourcebans', 'Protest ban');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Protest ban'),
		);
		
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
		$this->pageTitle = Yii::t('sourcebans', 'Error');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Error'),
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
		$this->pageTitle = Yii::t('sourcebans', 'Login');
		$this->breadcrumbs = array(
			Yii::t('sourcebans', 'Login'),
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
}