<?php

class BansController extends Controller
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
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_ADMINS")',
			),
			array('allow',
				'actions'=>array('delete'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("DELETE_ADMINS")',
			),
			array('allow',
				'actions'=>array('edit'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("EDIT_ALL_BANS", "EDIT_GROUP_BANS", "EDIT_OWN_BANS")',
			),
			array('allow',
				'actions'=>array('export'),
				'expression'=>'SourceBans::app()->settings->bans_public_export || (!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("OWNER"))',
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
		$model=new SBBan;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBBan']))
		{
			$model->attributes=$_POST['SBBan'];
			if($model->save())
				$this->redirect(array('site/bans','#'=>$model->id));
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

		$this->pageTitle=Yii::t('sourcebans', 'Bans');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'Administration') => array('admin/index'),
			Yii::t('sourcebans', 'Bans') => array('admin/bans'),
			$model->name,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('site/bans')),
		);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBBan']))
		{
			$model->attributes=$_POST['SBBan'];
			if($model->save())
				$this->redirect(array('site/bans','#'=>$model->id));
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

	public function actionImport()
	{
		$file = $_FILES['file'];
		
		foreach(file($file['tmp_name']) as $line)
		{
			list(, $length, $identity) = explode(' ', $line);
			// If this is not a permanent ban, ignore
			if($length)
				continue;
			
			// Steam ID
			if(preg_match(SourceBans::STEAM_PATTERN, $identity))
			{
				$ban         = new SBBan;
				$ban->type   = SBBan::STEAM_TYPE;
				$ban->steam  = $identity;
				$ban->reason = 'Imported banned_user.cfg';
				$ban->length = 0;
				$ban->save();
			}
			// IP address
			else if(preg_match(SourceBans::IP_PATTERN, $identity))
			{
				$ban         = new SBBan;
				$ban->type   = SBBan::IP_TYPE;
				$ban->ip     = $identity;
				$ban->reason = 'Imported banned_ip.cfg';
				$ban->length = 0;
				$ban->save();
			}
		}
		
		$this->redirect(array('site/bans'));
	}
	
	/**
	 * Exports permanent bans
	 */
	public function actionExport()
	{
		$type = Yii::app()->request->getQuery('type') == 'ip' ? SBBan::IP_TYPE : SBBan::STEAM_TYPE;
		$bans = SBBan::model()->permanent()->findAllByAttributes(array('type' => $type));
		
		header('Content-Type: application/x-httpd-php php');
		header('Content-Disposition: attachment; filename="banned_' . ($type == SBBan::IP_TYPE ? 'ip' : 'user') . '.cfg"');
		
		foreach($bans as $ban)
			printf("ban%s 0 %s\n", $type == SBBan::IP_TYPE ? 'ip' : 'id', $type == SBBan::IP_TYPE ? $ban->ip : $ban->steam);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SBBan the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SBBan::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param SBBan $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='ban-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
