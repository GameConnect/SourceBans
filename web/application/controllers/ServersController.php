<?php
class ServersController extends Controller
{
	const QUERY_INFO    = 1;
	const QUERY_PLAYERS = 2;
	const QUERY_RULES   = 4;
	
	
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
}