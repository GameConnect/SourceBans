<?php

class GroupsController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
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
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_GROUPS")',
			),
			array('allow',
				'actions'=>array('delete'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("DELETE_GROUPS")',
			),
			array('allow',
				'actions'=>array('edit'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("EDIT_GROUPS")',
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
		$class=Yii::app()->request->getQuery('type') == 'web' ? 'SBGroup' : 'SBServerGroup';
		$model=new $class;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST[$class]))
		{
			$model->attributes=$_POST[$class];
			if($model->save())
				$this->redirect(array('admin/groups','#'=>$model->id));
		}
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionEdit($id)
	{
		$type=Yii::app()->request->getQuery('type');
		$class=$type == 'web' ? 'SBGroup' : 'SBServerGroup';
		$model=$this->loadModel($id, $type);

		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.groups.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.groups.title') => array('admin/groups'),
			$model->name,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/groups')),
		);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST[$class]))
		{
			$model->attributes=$_POST[$class];
			if($model->save())
				$this->redirect(array('admin/groups','#'=>$model->id));
		}

		$this->render($type.'_edit',array(
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
		$this->loadModel($id, Yii::app()->request->getQuery('type'))->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	public function actionImport()
	{
		$file = $_FILES['file'];
		
		$kv = new KeyValues('Groups');
		$kv->load($file['tmp_name']);
		
		foreach($kv as $name => $data)
		{
			$server_group           = new SBServerGroup;
			$server_group->name     = $name;
			$server_group->flags    = isset($data['flags'])    ? $data['flags']    : '';
			$server_group->immunity = isset($data['immunity']) ? $data['immunity'] : 0;
			$server_group->save();
			
			if(isset($data['Overrides']))
			{
				foreach($data['Overrides'] as $name => $access)
				{
					// Parse name
					if($name{0} == ':')
					{
						$type = 'group';
						$name = substr($name, 1);
					}
					else
						$type = 'command';
					
					$override           = new SBServerGroupOverride;
					$override->group_id = $server_group->id;
					$override->type     = $type;
					$override->name     = $name;
					$override->access   = $access;
					$override->save();
				}
			}
		}
		
		$this->redirect(array('admin/groups'));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @param string $type the type of the model to be loaded
	 * @return SBGroup|SBServerGroup the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id, $type)
	{
		$class=$type == 'web' ? 'SBGroup' : 'SBServerGroup';
		$model=$class::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SBServerGroup $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && ($_POST['ajax']==='group-form' || $_POST['ajax']==='server-group-form'))
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
