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
				'actions'=>array('admins'),
				'expression'=>'!Yii::app()->user->isGuest && Yii::app()->user->data->hasPermission("LIST_ADMINS")',
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
			array('allow', // allow all users to perform 'getProfile', 'info', 'players', 'query' and 'rules' actions
				'actions'=>array('getProfile','info','players','query','rules'),
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
			{
				SourceBans::log('Server added', 'Server "' . $model . '" was added');
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->redirect(array('admin/servers','#'=>$model->id));
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

		$this->pageTitle=Yii::t('sourcebans', 'controllers.admin.servers.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.servers.title') => array('admin/servers'),
			$model,
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
			{
				SourceBans::log('Server edited', 'Server "' . $model . '" was edited');
				Yii::app()->user->setFlash('success', Yii::t('sourcebans', 'Saved successfully'));
				
				$this->redirect(array('admin/servers','#'=>$model->id));
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
		SourceBans::log('Server deleted', 'Server "' . $model . '" was deleted', SBLog::TYPE_WARNING);
		$model->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}
	
	public function actionAdmins($id)
	{
		$this->pageTitle=Yii::t('sourcebans', 'controllers.servers.admins.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.servers.title') => array('admin/servers'),
			Yii::t('sourcebans', 'controllers.servers.admins.title'),
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/servers')),
		);
		
		$admins = SBAdmin::model()->findAll(array(
			'condition' => 'servers.id = :server_id',
			'order' => 't.name',
			'params' => array(':server_id' => $id),
			'with' => array(
				'server_groups' => array('select' => false),
				'server_groups.servers' => array('select' => false),
			),
		));
		
		$this->render('admins',array(
			'admins'=>$admins,
		));
	}

	public function actionRcon($id)
	{
		$model=$this->loadModel($id);
		
		$this->pageTitle=Yii::t('sourcebans', 'controllers.servers.rcon.title');
		
		$this->breadcrumbs=array(
			Yii::t('sourcebans', 'controllers.admin.index.title') => array('admin/index'),
			Yii::t('sourcebans', 'controllers.admin.servers.title') => array('admin/servers'),
			$model,
		);
		
		$this->menu=array(
			array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/servers')),
		);
		
		if(empty($model->rcon) || (!$model->enabled && !Yii::app()->user->data->hasPermission('OWNER')))
			throw new CHttpException(403, Yii::t('yii', 'You are not authorized to perform this action.'));
		
		if(isset($_POST['command']))
			Yii::app()->end(CJSON::encode($this->_rconServer($_POST['command'], $id)));
		
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
	
	public function actionGetProfile()
	{
		$id       = Yii::app()->request->getQuery('id');
		$name     = Yii::app()->request->getPost('name');
		$response = $this->_rconServer('status', $id);
		if(isset($response['error']))
			Yii::app()->end(CJSON::encode($response));
		
		preg_match_all(SourceBans::PATTERN_STATUS, $response['result'], $players);
		for($i = 0; $i < count($players[0]); $i++)
		{
			if($players[3][$i] == $name)
			{
				Yii::app()->end(CJSON::encode(array(
					'id' => Helpers::getCommunityId($players[4][$i]),
				)));
			}
		}
		
		Yii::app()->end(CJSON::encode(array(
			'error' => array(
				'code'    => 'ERR_INVALID_NAME',
				'message' => Yii::t('sourcebans', 'controllers.servers.getProfile.err_invalid_name', array('{name}' => $name)),
			),
		)));
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
		
		preg_match_all('/([a-z_]+)=([^;]+)/i', Yii::app()->db->connectionString, $dsn);
		$db = array_combine($dsn[1], $dsn[2]);
		
		$this->render('config', array(
			'db'=>$db,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return SBServer the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=SBServer::model()->with('game')->findByPk($id);
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
		if(isset($_POST['ajax']) && $_POST['ajax']==='server-form')
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
			$servers = SBServer::model()->findAllByPk($id);
		else
			$servers = array(SBServer::model()->findByPk($id));
		
		$results = array();
		foreach($servers as $server)
		{
			$query  = new ServerQuery($server->host, $server->port);
			$result = array(
				'id'   => $server->id,
				'host' => $server->host,
				'port' => $server->port,
			);
			
			if($queries & self::QUERY_INFO)
			{
				$info = $query->getInfo();
				if(empty($info))
				{
					$result['error'] = array(
						'code'    => 'ERR_TIMEOUT',
						'message' => Yii::t('sourcebans', 'components.ServerQuery.err_timeout') . ' (' . $server . ')',
					);
				}
				else if($info['hostname'] == "anned by server\n")
				{
					$result['error'] = array(
						'code'    => 'ERR_BLOCKED',
						'message' => Yii::t('sourcebans', 'components.ServerQuery.err_blocked') . ' (' . $server . ')',
					);
				}
				else
				{
					$map_image = '/images/maps/' . $server->game->folder . '/' . $info['map'] . '.jpg';
					
					$result['hostname']   = preg_replace('/[\x00-\x1F]/', null, $info['hostname']); // Strip UTF-8 characters
					$result['numplayers'] = $info['numplayers'];
					$result['maxplayers'] = $info['maxplayers'];
					$result['map']        = basename($info['map']); // Strip Steam Workshop folder
					$result['os']         = $info['os'];
					$result['secure']     = $info['secure'];
					$result['map_image']  = file_exists(Yii::getPathOfAlias('webroot') . $map_image) ? Yii::app()->baseUrl . $map_image : null;
				}
			}
			if($queries & self::QUERY_PLAYERS)
			{
				$result['players'] = $query->getPlayers();
				usort($result['players'], array($this, '_sortPlayers'));
				
				foreach($result['players'] as &$player)
				{
					if($player['time'] > 60)
						$player['time'] = floor($player['time'] / 60) * 60; // Round off to minutes
					
					$player['time'] = Yii::app()->format->formatLength($player['time']);
				}
			}
			if($queries & self::QUERY_RULES)
			{
				$result['rules'] = $query->getRules();
			}
			
			$results[] = $result;
		}
		
		return empty($id) || is_array($id)
			? $results
			: $results[0];
	}
	
	private function _rconServer($command, $id = null)
	{
		if(empty($id))
			$servers = SBServer::model()->findAll();
		else if(is_array($id))
			$servers = SBServer::model()->findAllByPk($id);
		else
			$servers = array(SBServer::model()->findByPk($id));
		
		$results = array();
		foreach($servers as $server)
		{
			$rcon   = new ServerRcon($server->host, $server->port, $server->rcon);
			$result = array(
				'id'   => $server->id,
				'host' => $server->host,
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
		
		return empty($id) || is_array($id)
			? $results
			: $results[0];
	}
	
	private function _sortPlayers($a, $b)
	{
		// Sort score descending
		if($a['score'] != $b['score'])
			return $a['score'] > $b['score'] ? -1 : 1;
		
		// Sort time descending
		//if($a['time'] != $b['time'])
		//	return $a['time'] > $b['time'] ? -1 : 1;
		
		return strcasecmp($a['name'], $b['name']);
	}
}