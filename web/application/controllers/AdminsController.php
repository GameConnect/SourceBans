<?php

class AdminsController extends Controller
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
			'postOnly + add, delete, import', // we only allow deletion via POST request
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
				'actions'=>array('add'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_ADMINS")',
			),
			array('allow',
				'actions'=>array('delete'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("DELETE_ADMINS")',
			),
			array('allow',
				'actions'=>array('edit'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("EDIT_ADMINS")',
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
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionAdd()
	{
		$model=new SBAdmin;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBAdmin']))
		{
			$model->attributes=$_POST['SBAdmin'];
			if(!isset($_POST['SBAdmin']['server_groups']))
				$model->server_groups=array();
			if($model->save())
			{
				SourceBans::log('Admin added', 'Admin "' . $model->name . '" was added');
				$this->redirect(array('admin/admins','#'=>$model->id));
			}
		}
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionEdit($id)
	{
		$model=$this->loadModel($id);

		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.admins.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.admins.title') => array('admin/admins'),
			$model->name,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/admins')),
		);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBAdmin']))
		{
			$model->attributes=$_POST['SBAdmin'];
			if(!isset($_POST['SBAdmin']['server_groups']))
				$model->server_groups=array();
			if($model->save())
			{
				SourceBans::log('Admin edited', 'Admin "' . $model->name . '" was edited');
				$this->redirect(array('admin/admins','#'=>$model->id));
			}
		}

		$this->render('edit',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$model=$this->loadModel($id);
		SourceBans::log('Admin deleted', 'Admin "' . $model->name . '" was deleted', SBLog::WARNING_TYPE);
		$model->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	public function actionImport()
	{
		$file = $_FILES['file'];
		
		switch($file['name'])
		{
			// SourceMod
			case 'admins.cfg':
			case 'admins_simple.ini':
				$server_groups = CHtml::listData(SBServerGroup::model()->findAll(), 'name', 'id');
				
				// Detailed
				if($file['name'] == 'admins.cfg')
				{
					$kv = new KeyValues('Admins');
					$kv->load($file['tmp_name']);
					
					foreach($kv as $name => $data)
					{
						$admin           = new SBAdmin;
						$admin->name     = $name;
						$admin->auth     = $data['auth'];
						$admin->identity = $data['identity'];
						
						if(isset($data['password']))
						{
							$admin->setPassword($data['password']);
							$admin->server_password = $data['password'];
						}
						if(isset($data['group']))
						{
							$admin->server_groups = array();
							foreach((array)$data['group'] as $group)
							{
								$admin->server_groups = array_merge($admin->server_groups, array($server_groups[$group]));
							}
						}
						
						$admin->save();
					}
				}
				// Simple
				else
				{
					preg_match_all('/"(.+?)"[ \t]*"(.+?)"([ \t]*"(.+?)")?/', file_get_contents($file['tmp_name']), $admins);
					
					for($i = 0; $i < count($admins[0]); $i++)
					{
						list($identity, $flags, $password) = array($admins[1][$i], $admins[2][$i], $admins[4][$i]);
						
						// Parse authentication type depending on identity
						if(preg_match(SourceBans::STEAM_PATTERN, $identity))
							$auth = SBAdmin::STEAM_AUTH;
						else if($identity{0} == '!' && preg_match(SourceBans::IP_PATTERN, $identity))
							$auth = SBAdmin::IP_AUTH;
						else
							$auth = SBAdmin::NAME_AUTH;
						
						// Parse flags
						if($flags{0} == '@')
						{
							$group = substr($flags, 1);
						}
						else if(strpos($flags, ':') !== false)
						{
							list($immunity, $flags) = explode(':', $flags);
						}
						
						$admin           = new SBAdmin;
						$admin->name     = $identity;
						$admin->auth     = $auth;
						$admin->identity = $identity;
						
						if(isset($password))
						{
							$admin->setPassword($password);
							$admin->server_password = $password;
						}
						if(isset($group))
						{
							$admin->server_groups = array($server_groups[$group]);
						}
						
						$admin->save();
					}
				}
				
				break;
			// Mani Admin Plugin
			case 'clients.txt':
				$kv = new KeyValues;
				$kv->load($file['tmp_name']);
				
				foreach($kv['players'] as $name => $player)
				{
					$admin           = new SBAdmin;
					$admin->auth     = SBAdmin::STEAM_AUTH;
					$admin->name     = $name;
					$admin->identity = $player['steam'];
					$admin->save();
				}
				
				break;
			default:
				throw new CHttpException(500, Yii::t('sourcebans', 'controllers.admins.import.error'));
		}
		
		SourceBans::log('Admins imported', 'Admins imported from ' . $file['name']);
		$this->redirect(array('admin/admins'));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SBAdmin the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SBAdmin::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SBAdmin $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='admin-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
