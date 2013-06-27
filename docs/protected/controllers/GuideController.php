<?php
class GuideController extends CController
{
	public $defaultAction = 'view';
	private $_languages;
	private $_language;
	private $_sections;
	private $_topics;
	private $_versions;
	
	public function actionView()
	{
		$topic = $this->getTopic();
		$file = Yii::getPathOfAlias('application.data.guide') . DIRECTORY_SEPARATOR . $this->getVersion() . DIRECTORY_SEPARATOR . $this->getLanguage() . DIRECTORY_SEPARATOR . $topic . '.txt';
		if(!is_file($file))
			$file = Yii::getPathOfAlias('application.data.guide') . DIRECTORY_SEPARATOR . $this->getVersion() . DIRECTORY_SEPARATOR . $topic . '.txt';
		
		if(!strcasecmp($topic, 'toc') || !is_file($file))
			throw new CHttpException(404, 'The requested page does not exist.');
		
		$content = file_get_contents($file);
		$markdown = new MarkdownParser;
		$markdown->purifierOptions['Attr.EnableID'] = true;
		$content = $markdown->safeTransform($content);
		
		$highlightCss = Yii::app()->assetManager->publish($markdown->getDefaultCssFile());
		Yii::app()->clientScript->registerCssFile($highlightCss);
		
		$imageUrl = Yii::app()->baseUrl . '/images/guide';
		$content = preg_replace('/<p>\s*<img(.*?)src="(.*?)"\s+alt="(.*?)"\s*\/>\s*<\/p>/',
			'<div class="image"><p>\\3</p><img\\1src="' . $imageUrl . '/\\2" alt="\\3" /></div>', $content);
		
		$content = preg_replace_callback('/href="\/doc\/guide\/(.*?)\/?"/', array($this, 'replaceGuideLink'), $content);
		$content = preg_replace('/href="(\/doc\/api\/.*?)"/', 'href="http://sourcebans.net$1"', $content);
		
		$content = preg_replace_callback('/<h2([^>]*)>(.*?)<\/h2>/', array($this, 'replaceSection'), $content);
		$content = preg_replace_callback('/<h1([^>]*)>(.*?)<\/h1>/', array($this, 'replaceTitle'), $content, 1);
		
		$this->pageTitle = 'User Guide « SourceBans';
		if($topic !== 'index' && preg_match('/<h1[^>]*>(.*?)</', $content, $matches))
			$this->pageTitle = CHtml::encode($matches[1]) . ' « ' . $this->pageTitle;
		
		$this->render('view', array(
			'content' => $content,
		));
	}
	
	public function replaceGuideLink($matches)
	{
		if(($pos = strpos($matches[1],'#')) !== false)
		{
			$anchor = substr($matches[1], $pos);
			$matches[1] = substr($matches[1], 0, $pos);
		}
		else
			$anchor = '';
		return 'href="' . $this->createUrl('view', array('lang' => $this->language, 'page' => $matches[1])) . $anchor . '"';
	}
	
	public function replaceSection($matches)
	{
		$id = preg_replace('/[^a-z0-9]/', '-', strtolower($matches[2]));
		$id = str_replace('/-{2,}/', '-', $id);
		$this->_sections[$id] = $matches[2];
		
		return $matches[0];
	}
	
	public function replaceTitle($matches)
	{
		if(!empty($this->_sections))
		{
			$sections = "\n" . '<div class="toc"><ol>';
			foreach($this->_sections as $id => $section)
			{
				$sections .= "\n" . '<li><a href="#' . $id . '">' . $section . '</a></li>';
			}
			$sections .= "\n" . '</ol></div>';
		}
		else
			$sections = '';
		
		return '<h1' . $matches[1] . '>' . $matches[2] . '</h1>' . $sections;
	}
	
	public function getTopic()
	{
		if(isset($_GET['page']) && !empty($_GET['page']))
			return str_replace(array('/', '\\'), '', trim($_GET['page']));
		
		return 'index';
	}
	
	public function getTopics()
	{
		if($this->_topics === null)
		{
			$file = Yii::getPathOfAlias('application.data.guide') . DIRECTORY_SEPARATOR . $this->getVersion() . DIRECTORY_SEPARATOR . $this->getLanguage() . DIRECTORY_SEPARATOR . 'toc.txt';
			if(!is_file($file))
				$file = Yii::getPathOfAlias('application.data.guide') . DIRECTORY_SEPARATOR . $this->getVersion() . DIRECTORY_SEPARATOR . 'toc.txt';
			
			$lines = file($file);
			$chapter = '';
			foreach($lines as $line)
			{
				if(($line = trim($line)) === '')
					continue;
				
				if($line[0] === '*')
					$chapter = trim($line, '* ');
				else if($line[0]==='-' && preg_match('/\[(.*?)\]\((.*?)\)/', $line, $matches))
					$this->_topics[$chapter][$matches[2]] = $matches[1];
			}
		}
		
		return $this->_topics;
	}
	
	public function getLanguage()
	{
		if($this->_language === null)
		{
			if(isset($_GET['lang']) && preg_match('/^[a-z_]+$/', $_GET['lang']))
				$this->_language=$_GET['lang'];
			else
				$this->_language='en';
		}
		
		return $this->_language;
	}
	
	public function getLanguages()
	{
		if($this->_languages === null)
		{
			$basePath = Yii::getPathOfAlias('application.data.guide') . DIRECTORY_SEPARATOR . $this->getVersion();
			$dir = opendir($basePath);
			$this->_languages = array('en' => 'English');
			if($this->language !== 'en')
				$this->_languages['en'] .= ' (' . CLocale::getInstance($this->language)->getLocaleDisplayName('en') . ')';
			
			while(($file = readdir($dir)) !== false)
			{
				if(!is_dir($basePath.DIRECTORY_SEPARATOR.$file) || $file==='.' || $file==='..' || $file==='source')
					continue;
				
				$this->_languages[$file] = CLocale::getInstance($file)->getLocaleDisplayName($file);
				if($file !== $this->language)
					$this->_languages[$file] .= ' (' . CLocale::getInstance($this->language)->getLocaleDisplayName($file) . ')';
			}
			ksort($this->_languages);
		}
		
		return $this->_languages;
	}
	
	public function getVersion()
	{
		return Yii::app()->request->getQuery('version', Yii::app()->params['defaultVersion']);
	}
	
	public function getVersions()
	{
		if($this->_versions === null)
		{
			$basePath = Yii::getPathOfAlias('application.data.guide');
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