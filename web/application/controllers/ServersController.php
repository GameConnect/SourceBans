<?php
class ServersController extends Controller
{
	const QUERY_INFO    = 1;
	const QUERY_PLAYERS = 2;
	const QUERY_RULES   = 4;
	
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
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("ADD_SERVERS")',
			),
			array('allow',
				'actions'=>array('delete'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("DELETE_SERVERS")',
			),
			array('allow',
				'actions'=>array('edit'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("EDIT_SERVERS")',
			),
			array('allow',
				'actions'=>array('config'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasFlag(SM_CONFIG)',
			),
			array('allow',
				'actions'=>array('kick'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasFlag(SM_KICK)',
			),
			array('allow',
				'actions'=>array('rcon'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasFlag(SM_RCON)',
			),
			array('allow', // allow all users to perform 'info', 'players', 'query' and 'rules' actions
				'actions'=>array('info','players','query','rules'),
				'users'=>array('*'),
			),
			array('allow',
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionInfo()
	{
		$id       = Yii::app()->request->getQuery('id');
		$response = $this->_queryServer(self::QUERY_INFO, $id);
		
		Yii::app()->end(CJSON::encode($response));
	}
	
	public function actionPlayers()
	{
		$id       = Yii::app()->request->getQuery('id');
		$response = $this->_queryServer(self::QUERY_PLAYERS, $id);
		
		Yii::app()->end(CJSON::encode($response));
	}
	
	public function actionQuery()
	{
		$id       = Yii::app()->request->getQuery('id');
		$response = $this->_queryServer(self::QUERY_INFO|self::QUERY_PLAYERS, $id);
		
		Yii::app()->end(CJSON::encode($response));
	}
	
	public function actionRules()
	{
		$id       = Yii::app()->request->getQuery('id');
		$response = $this->_queryServer(self::QUERY_RULES, $id);
		
		Yii::app()->end(CJSON::encode($response));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionAdd()
	{
		$model=new SBServer;

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBServer']))
		{
			$model->attributes=$_POST['SBServer'];
			if(!isset($_POST['SBServer']['groups']))
				$model->groups=array();
			if($model->save())
				$this->redirect(array('admin/servers','#'=>$model->id));
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

		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.servers.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.servers.title') => array('admin/servers'),
			$model->address,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/servers')),
		);

		// Uncomment the following line if AJAX validation is needed
		$this->performAjaxValidation($model);

		if(isset($_POST['SBServer']))
		{
			$model->attributes=$_POST['SBServer'];
			if(!isset($_POST['SBServer']['groups']))
				$model->groups=array();
			if($model->save())
				$this->redirect(array('admin/servers','#'=>$model->id));
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

	public function actionRcon($id)
	{
		$model=$this->loadModel($id);
		
		$this->pageTitle=Yii::t('sourcebans', 'controllers.servers.rcon.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.servers.title') => array('admin/servers'),
			$model->address,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/servers')),
		);
		
		if(empty($model->rcon) || (!$model->enabled && !Yii::app()->user->data->hasPermission('OWNER')))
			throw new CHttpException(403);
		
		if(isset($_POST['command']))
			Yii::app()->end($this->_rconServer($_POST['command'], $id));
		
		$this->render('rcon', array(
			'model'=>$model,
		));
	}
	
	public function actionKick()
	{
		$id       = Yii::app()->request->getQuery('id');
		$name     = Yii::app()->request->getPost('name');
		$response = $this->_rconServer('kick "' . addslashes($name) . '"', $id);
		
		Yii::app()->end(CJSON::encode($response));
	}
	
	public function actionConfig()
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.servers.config.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.servers.title') => array('admin/servers'),
			Yii::t('sourcebans', 'controllers.servers.config.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/servers')),
		);
		
		$this->render('config');
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
		$model=SBServer::model()->findByPk($id);
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
	
	
	private function _queryServer($queries, $id = null)
	{
		if(empty($id))
			$servers = SBServer::model()->findAll();
		else if(is_array($id))
			$servers = SBServer::model()->findByPk($id);
		else
			$servers = array(SBServer::model()->findByPk($id));
		
		$results = array();
		foreach($servers as $server)
		{
			$query  = new ServerQuery($server->ip, $server->port);
			$result = array(
				'id'   => $server->id,
				'ip'   => $server->ip,
				'port' => $server->port,
			);
			
			if($queries & self::QUERY_INFO)
			{
				$info   = $query->getInfo();
				if(empty($info))
				{
					$result['error'] = array(
						'code'    => 'ERR_TIMEOUT',
						'message' => Yii::t('sourcebans', 'Error connecting ({ip}:{port})', array('{ip}' => $server->ip, '{port}' => $server->port)),
					);
				}
				else if($info['hostname'] == "anned by server\n")
				{
					$result['error'] = array(
						'code'    => 'ERR_BLOCKED',
						'message' => Yii::t('sourcebans', 'Banned by server ({ip}:{port})', array('{ip}' => $server->ip, '{port}' => $server->port)),
					);
				}
				else
				{
					$map_image = '/images/maps/' . $server->game->folder . '/' . $info['map'] . '.jpg';
					
					$result['hostname']   = preg_replace('/[\x00-\x1F\x7F-\x9F]/', null, $info['hostname']);
					$result['numplayers'] = $info['numplayers'];
					$result['maxplayers'] = $info['maxplayers'];
					$result['map']        = $info['map'];
					$result['os']         = $info['os'];
					$result['secure']     = $info['secure'];
					$result['map_image']  = file_exists(Yii::getPathOfAlias('webroot') . $map_image) ? Yii::app()->baseUrl . $map_image : null;
				}
			}
			if($queries & self::QUERY_PLAYERS)
			{
				$result['players'] = $query->getPlayers();
				foreach($result['players'] as &$player)
				{
					$player['time'] = Yii::app()->format->formatLength($player['time']);
				}
				
				Helpers::array_qsort($result['players'], 'score', SORT_DESC);
			}
			if($queries & self::QUERY_RULES)
			{
				$result['rules'] = $query->getRules();
			}
			
			$results[] = $result;
		}
		
		return empty($id)
			? $results
			: $results[0];
	}
	
	private function _rconServer($command, $id = null)
	{
		if(empty($id))
			$servers = SBServer::model()->findAll();
		else if(is_array($id))
			$servers = SBServer::model()->findByPk($id);
		else
			$servers = array(SBServer::model()->findByPk($id));
		
		$results = array();
		foreach($servers as $server)
		{
			$rcon   = new ServerRcon($server->ip, $server->port, $server->rcon);
			$result = array(
				'id'   => $server->id,
				'ip'   => $server->ip,
				'port' => $server->port,
			);
			
			if(!$rcon->auth())
			{
				$result['error'] = array(
					'code'    => 'ERR_INVALID_PASSWORD',
					'message' => Yii::t('yii', '{attribute} is invalid.', array('{attribute}' => $server->getAttributeLabel('rcon'))),
				);
			}
			else
			{
				$result['result'] = $rcon->execute($command);
			}
			
			$results[] = $result;
		}
		
		return empty($id)
			? $results
			: $results[0];
	}
}