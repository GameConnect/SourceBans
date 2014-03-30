<?php

class GamesController extends Controller
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
				'actions'=>array('add','mapImage'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_GAMES")',
			),
			array('allow',
				'actions'=>array('delete'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("DELETE_GAMES")',
			),
			array('allow',
				'actions'=>array('edit'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("EDIT_GAMES")',
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
		$model=new SBGame;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBGame']))
		{
			$model->attributes=$_POST['SBGame'];
			if($model->save())
			{
				SourceBans::log('Game added', 'Game "' . $model . '" was added');
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->redirect(array('admin/games','#'=>$model->id));
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

		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.games.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.games.title') => array('admin/games'),
			$model,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/games')),
		);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBGame']))
		{
			$model->attributes=$_POST['SBGame'];
			if($model->save())
			{
				SourceBans::log('Game edited', 'Game "' . $model . '" was edited');
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->redirect(array('admin/games','#'=>$model->id));
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
		SourceBans::log('Game deleted', 'Game "' . $model . '" was deleted', SBLog::TYPE_WARNING);
		$model->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Uploads a map image.
	 */
	public function actionMapImage()
	{
		$model=new MapImageForm;

		if(isset($_POST['MapImageForm']))
		{
			$model->attributes=$_POST['MapImageForm'];
			if($model->save())
			{
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->redirect(array('admin/games','#'=>'map-image'));
			}
		}
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SBGame the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SBGame::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SBGame $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='game-form')
		{
			echo CActiveForm::validate($model,array('name','folder'));
			Yii::app()->end();
		}
	}
}
