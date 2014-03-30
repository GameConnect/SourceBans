<?php

class PluginsController extends Controller
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
			'postOnly + disable, enable, install, uninstall', // we only allow deletion via POST request
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
				'actions'=>array('disable','enable','install','settings','uninstall'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("SETTINGS")',
			),
			array('allow',
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionDisable($id)
	{
		$plugin=$this->loadModel($id);
		$plugin->status=SBPlugin::STATUS_INSTALLED;
		
		Yii::app()->end(CJSON::encode($plugin->save()));
	}

	public function actionEnable($id)
	{
		$plugin=$this->loadModel($id);
		$plugin->status=SBPlugin::STATUS_ENABLED;
		
		Yii::app()->end(CJSON::encode($plugin->save()));
	}

	public function actionInstall($id)
	{
		$plugin=$this->loadModel($id);
		
		try
		{
			$plugin->runInstall();
			$plugin->status=!$plugin->canDisable()
				?SBPlugin::STATUS_ENABLED
				:SBPlugin::STATUS_INSTALLED;
			$plugin->save();
			SourceBans::log('Plugin installed', 'Plugin "' . $plugin . '" was installed');
		}
		catch(Exception $e)
		{
			Yii::app()->end(CJSON::encode(array(
				'error' => array(
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
				),
			)));
		}
		
		Yii::app()->end(CJSON::encode(true));
	}

	public function actionSettings($id)
	{
		$plugin=$this->loadModel($id);
		
		$this->pageTitle=$plugin;
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.settings.menu.plugins') => array('admin/settings', '#'=>'plugins'),
			$plugin,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/settings','#'=>'plugins')),
		);
		
		$data = (array)$plugin->runSettings();
		$data['plugin'] = $plugin;
		
		$this->render($plugin->getViewFile('settings'), $data);
	}

	public function actionUninstall($id)
	{
		$plugin=$this->loadModel($id);
		
		try
		{
			$plugin->runUninstall();
			$plugin->status=0;
			$plugin->save();
			SourceBans::log('Plugin uninstalled', 'Plugin "' . $plugin . '" was uninstalled', SBLog::TYPE_WARNING);
		}
		catch(Exception $e)
		{
			Yii::app()->end(CJSON::encode(array(
				'error' => array(
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
				),
			)));
		}
		
		Yii::app()->end(CJSON::encode(true));
	}

	/**
	 * Returns the data model based on the id given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param string $id the ID of the model to be loaded
	 * @return SBPlugin the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SBPlugin::model()->findById($id);
		if($model===null)
			throw new CHttpException(404, 'The requested page does not exist.');
		return $model;
	}
}
