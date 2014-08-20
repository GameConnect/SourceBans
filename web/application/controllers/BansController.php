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
			'postOnly + add, delete, import, unban', // we only allow deletion via POST request
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
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_BANS")',
			),
			array('allow',
				'actions'=>array('delete'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("DELETE_BANS")',
			),
			array('allow',
				'actions'=>array('edit'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("EDIT_ALL_BANS", "EDIT_GROUP_BANS", "EDIT_OWN_BANS")',
			),
			array('allow',
				'actions'=>array('unban'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("UNBAN_ALL_BANS", "UNBAN_GROUP_BANS", "UNBAN_OWN_BANS")',
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
			{
				$demo=new SBDemo;
				$demo->object_type=SBDemo::TYPE_BAN;
				$demo->object_id=$model->id;
				$demo->save();
				
				SourceBans::log('Ban added', 'Ban against "' . $model . '" was added');
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->redirect(array('default/bans','#'=>$model->id));
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

		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.bans.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.bans.title') => array('admin/bans'),
			$model,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('default/bans')),
		);
		
		if(!$this->canUpdate('EDIT', $model))
			throw new CHttpException(403, Yii::t('yii', 'You are not authorized to perform this action.'));

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBBan']))
		{
			$model->attributes=$_POST['SBBan'];
			if($model->save())
			{
				SourceBans::log('Ban edited', 'Ban against "' . $model . '" was edited');
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->redirect(array('default/bans','#'=>$model->id));
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
		SourceBans::log('Ban deleted', 'Ban against "' . $model . '" was deleted', SBLog::TYPE_WARNING);
		$model->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}

	/**
	 * Unbans a particular model.
	 * @param integer $id the ID of the model to be unbanned
	 * @throws CHttpException If the user is not authorized to perform this action
	 */
	public function actionUnban($id)
	{
		$reason=Yii::app()->request->getPost('reason');
		$model=$this->loadModel($id);
		
		if(!$this->canUpdate('UNBAN', $model))
			throw new CHttpException(403, Yii::t('yii', 'You are not authorized to perform this action.'));
		
		SourceBans::log('Ban unbanned', 'Ban against "' . $model . '" was unbanned');
		Yii::app()->end(CJSON::encode($model->unban($reason)));
	}

	public function actionImport()
	{
		$file = $_FILES['file'];
		
		switch($file['name'])
		{
			// Source Dedicated Server
			case 'banned_ip.cfg':
			case 'banned_user.cfg':
				foreach(file($file['tmp_name']) as $line)
				{
					list(, $length, $identity) = explode(' ', rtrim($line));
					// If this is not a permanent ban, ignore
					if($length)
						continue;
					
					// Steam ID
					if(preg_match(SourceBans::PATTERN_STEAM, $identity))
					{
						$ban         = new SBBan;
						$ban->type   = SBBan::TYPE_STEAM;
						$ban->steam  = $identity;
						$ban->reason = 'Imported from banned_user.cfg';
						$ban->length = 0;
						$ban->save();
					}
					// IP address
					else if(preg_match(SourceBans::PATTERN_IP, $identity))
					{
						$ban         = new SBBan;
						$ban->type   = SBBan::TYPE_IP;
						$ban->ip     = $identity;
						$ban->reason = 'Imported from banned_ip.cfg';
						$ban->length = 0;
						$ban->save();
					}
				}
				break;
			// ESEA Ban List
			case 'esea_ban_list.csv':
				$handle = fopen($file['tmp_name'], 'r');
				while(list($steam, $name) = fgetcsv($handle, 4096))
				{
					$steam = 'STEAM_' . trim($steam);
					if(!preg_match(SourceBans::PATTERN_STEAM, $steam))
						continue;
					
					$ban         = new SBBan;
					$ban->type   = SBBan::TYPE_STEAM;
					$ban->steam  = $steam;
					$ban->name   = $name;
					$ban->reason = 'Imported from esea_ban_list.csv';
					$ban->length = 0;
					$ban->save();
				}
				
				fclose($handle);
				break;
			default:
				throw new CHttpException(500, Yii::t('sourcebans', 'controllers.bans.import.error'));
		}
		
		SourceBans::log('Bans imported', 'Bans imported from ' . $file['name']);
		Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Imported successfully'));
		
		$this->redirect(array('default/bans'));
	}
	
	/**
	 * Exports permanent bans
	 */
	public function actionExport()
	{
		$type = Yii::app()->request->getQuery('type') == 'ip' ? SBBan::TYPE_IP : SBBan::TYPE_STEAM;
		$bans = SBBan::model()->permanent()->findAllByAttributes(array('type' => $type));
		
		header('Content-Type: application/x-httpd-php php');
		header('Content-Disposition: attachment; filename="banned_' . ($type == SBBan::TYPE_IP ? 'ip' : 'user') . '.cfg"');
		
		foreach($bans as $ban)
			printf("ban%s 0 %s\n", $type == SBBan::TYPE_IP ? 'ip' : 'id', $type == SBBan::TYPE_IP ? $ban->ip : $ban->steam);
	}

	public function canUpdate($type, $model)
	{
		if(Yii::app()->user->data->hasPermission($type . '_ALL_BANS'))
			return true;
		
		if(Yii::app()->user->data->hasPermission($type . '_GROUP_BANS') && isset($model->admin))
		{
			$groups = CHtml::listData($model->admin->server_groups, 'id', 'name');
			if(Yii::app()->user->data->hasGroup($groups))
				return true;
		}
		
		return Yii::app()->user->data->hasPermission($type . '_OWN_BANS') && Yii::app()->user->id == $model->admin_id;
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
		$model=SBBan::model()->with('admin')->findByPk($id);
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
