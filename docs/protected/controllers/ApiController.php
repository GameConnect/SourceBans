<?php
class ApiController extends CController
{
	public $defaultAction = 'view';
	private $_versions;
	
	public function actionView()
	{
		$topic = $this->getTopic();
		$file = Yii::getPathOfAlias('application.data.api') . DIRECTORY_SEPARATOR . $this->getVersion() . DIRECTORY_SEPARATOR . $topic . '.html';
		if(!is_file($file))
			throw new CHttpException(404, 'The requested page does not exist.');
		
		$content = file_get_contents($file);
		
		$this->pageTitle = 'API « SourceBans';
		if($topic !== 'index' && preg_match('/<h1[^>]*>(.*?)</', $content, $matches))
			$this->pageTitle = CHtml::encode($matches[1]) . ' « ' . $this->pageTitle;
		
		$this->render('view', array(
			'content' => $content,
		));
	}
	
	public function actionSearch()
	{
		$q = Yii::app()->request->getQuery('q');
		if(strlen($q) < 3)
			Yii::app()->end(CJSON::encode(false));
		
		$file = Yii::getPathOfAlias('application.data.api') . DIRECTORY_SEPARATOR . $this->getVersion() . DIRECTORY_SEPARATOR . 'keywords.txt';
		if(!is_file($file))
			Yii::app()->end(CJSON::encode(false));
		
		$keywords = explode(',', file_get_contents($file));
		$keywords = array_filter($keywords, function($keyword) use ($q) {
			return stripos($keyword, $q) !== false;
		});
		
		Yii::app()->end(CJSON::encode($keywords));
	}
	
	public function getTopic()
	{
		if(isset($_GET['page']) && !empty($_GET['page']))
			return str_replace(array('/', '\\'), '', trim($_GET['page']));
		
		return 'index';
	}
	
	public function getVersion()
	{
		return Yii::app()->request->getQuery('version', Yii::app()->params['defaultVersion']);
	}
	
	public function getVersions()
	{
		if($this->_versions === null)
		{
			$basePath = Yii::getPathOfAlias('application.data.api');
			$dir = opendir($basePath);
			while(($file = readdir($dir)) !== false)
			{
				if(!is_dir($basePath.DIRECTORY_SEPARATOR.$file) || $file==='.' || $file==='..' || $file==='source')
					continue;
				
				$this->_versions[] = $file;
			}
			rsort($this->_versions);
		}
		
		return $this->_versions;
	}
}