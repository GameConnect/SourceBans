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
			'postOnly + add, delete', // we only allow deletion via POST request
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
				$this->redirect(array('admin/admins','#'=>$model->id));
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

		$this->pageTitle=Yii::t('sourcebans', 'Admins');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Admins') => array('admin/admins'),
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
				$this->redirect(array('admin/admins','#'=>$model->id));
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
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
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
