<?php
ini_set('memory_limit', '1024M');

/**
 * DocsCommand class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
Yii::import('application.commands.docs.DocsModel');
/**
 * BUILD_PATH refers to the application base path
 */
defined("BUILD_PATH") or define("BUILD_PATH", dirname(dirname(__FILE__)));

/**
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: ApiCommand.php 2942 2011-02-08 11:42:22Z mdomba $
 * @package system.build
 * @since 1.0
 */
class DocsCommand extends CConsoleCommand
{
	const URL_PATTERN='/\{\{([^\}]+)\|([^\}]+)\}\}/';
	public $classes;
	public $views;
	public $packages;
	public $pageTitle;
	public $themePath;
	public $currentClass;
	public $currentView;
	public $baseSourceUrl="https://github.com/GameConnect/SourceBans/blob";
	public $frameworkSourceUrl="https://github.com/yiisoft/yii/blob";
	public $version;
	public $enableViews = true;
	public $appOptions = array();
	public $yiiOptions = array();
	public function getHelp()
	{
		return <<<EOD
USAGE
  yiic docs <output-path> [mode]
  yiic docs check

DESCRIPTION
  This command generates offline API documentation for the Yii framework.

PARAMETERS
  * output-path: required, the directory where the generated documentation would be saved.
  * mode: optional, either 'online' or 'offline' (default).
          Indicates whether the generated documentation are for online or offline use.

  * check: check PHPDoc for proper @param syntax

EXAMPLES
  * yiic docs yii/doc        	- builds api documentation in folder yii/doc
  * yiic docs yii/doc noviews 	- disables documentation of views
  * yiic docs check          	- cheks PHPDoc @param directives

EOD;
	}

	/**
	 * Execute the action.
	 * @param array command line parameters specific for this command
	 */
	public function run($args)
	{
		$this->appOptions=array(
			'fileTypes'=>array('php'),
			'exclude'=>array(
				'.svn',
				'/yiic.php',
				'/commands/docs',
				'/commands/DocsCommand',
				'/config',
				'/controllers',
				'/data',
				'/extensions',
				'/messages',
				'/migrations',
				'/modules',
				'/plugins',
				'/tests',
				'/vendors',
				'/views',
				
			),
		);
		$this->yiiOptions=array(
			'fileTypes'=>array('php'),
			'exclude'=>array(
				'.svn',
				'/yiilite.php',
				'/yiit.php',
				'/cli',
				'/i18n/data',
				'/messages',
				'/vendors',
				'/views',
				'/web/js',
				'/web/widgets/views',
				'/utils/mimeTypes.php',
				'/gii/assets',
				'/gii/components',
				'/gii/controllers',
				'/gii/generators',
				'/gii/models',
				'/gii/views',
				'/test',
			),
		);
		if(!isset($args[0]))
			$this->usageError('the output directory is not specified.');

		if($args[0]=='check') {
			$checkFiles = CFileHelper::findFiles(BUILD_PATH,$this->appOptions);
			
			$model=new DocsModel;
			$model->check($checkFiles);
			exit();
		}

		if(!is_dir($docPath=$args[0]))
			$this->usageError("the output directory {$docPath} does not exist.");
		
		if (isset($args[1]) && $args[1] == "noviews") {
			$this->enableViews = false;
			
		}
		$offline=true;
		if(isset($args[1]) && $args[1]==='online')
			$offline=false;

		$this->version=SourceBans::getVersion();

		/*
		 * development version - link to trunk
		 * release version link to tags
		 */
		if(substr($this->version,-3)=='dev')
			$this->baseSourceUrl .= '/master/web/application';
		else
			$this->baseSourceUrl .= '/'.$this->version.'/web/application';

		if(substr(Yii::getVersion(),-3)=='dev')
			$this->frameworkSourceUrl .= '/master/framework';
		else
			$this->frameworkSourceUrl .= '/'.Yii::getVersion().'/framework';

		$this->pageTitle='API « '.Yii::app()->name;
		$themePath=dirname(__FILE__).'/docs';

		echo "\nBuilding.. : " . Yii::app()->name ."\n";
		echo "Type...... : " . ( $offline ? "offline" : "online" ). "\n";
		echo "Version... : " . $this->version." (using Yii ".Yii::getVersion().")\n";
		echo "Source URL : " . $this->baseSourceUrl."\n\n";

		echo "Building model...\n";
		$model=$this->buildModel(BUILD_PATH);
		$this->classes=$model->classes;
		$this->packages=$model->packages;
		$this->views = $model->views;
		echo "Building pages...\n";
		if($offline)
			$this->buildOfflinePages($docPath.DIRECTORY_SEPARATOR.'api',$themePath);
		else
		{
			$this->buildOnlinePages($docPath.DIRECTORY_SEPARATOR.'api',$themePath);
			$this->buildKeywords($docPath);
			$this->buildPackages($docPath);
		}
		echo "Done.\n\n";
	}

	protected function buildPackages($docPath)
	{
		file_put_contents($docPath.'/api/packages.txt',serialize($this->packages));
	}

	protected function buildKeywords($docPath)
	{
		$keywords=array();
		foreach($this->classes as $class)
			$keywords[]=$class->name;
		foreach($this->classes as $class)
		{
			$name=$class->name;
			foreach($class->properties as $property)
			{
				if(!$property->isInherited)
					$keywords[]=$name.'.'.$property->name;
			}
			foreach($class->methods as $method)
			{
				if(!$method->isInherited)
					$keywords[]=$name.'.'.$method->name.'()';
			}
		}
		file_put_contents($docPath.'/api/keywords.txt',implode(',',$keywords));
	}

	public function render($view,$data=null,$return=false,$layout='main')
	{
		$viewFile=$this->themePath."/views/{$view}.php";
		$layoutFile=$this->themePath."/layouts/{$layout}.php";
		$content=$this->renderFile($viewFile,$data,true);
		return $this->renderFile($layoutFile,array('content'=>$content),$return);
	}

	public function renderPartial($view,$data=null,$return=false)
	{
		$viewFile=$this->themePath."/views/{$view}.php";
		return $this->renderFile($viewFile,$data,$return);
	}

	public function renderSourceLink($sourcePath,$line=null)
	{
		
		if (file_exists(BUILD_PATH.$sourcePath)) {
			if ($this->baseSourceUrl === false) {
				if($line===null)
					return 'application'.$sourcePath;
				else
					return 'applocation'.$sourcePath.'#L'.$line;
			}
			else {
				if($line===null)
					return CHtml::link('application'.$sourcePath,$this->baseSourceUrl.$sourcePath,array('class'=>'sourceLink','target'=>'_blank'));
				else
					return CHtml::link('application'.$sourcePath.'#L'.$line, $this->baseSourceUrl.$sourcePath.'#L'.$line,array('class'=>'sourceLink','target'=>'_blank'));
			}
		}
		else {
			if($line===null)
				return CHtml::link('framework'.$sourcePath,$this->frameworkSourceUrl.$sourcePath,array('class'=>'sourceLink','target'=>'_blank'));
			else
				return CHtml::link('framework'.$sourcePath.'#L'.$line, $this->frameworkSourceUrl.$sourcePath.'#L'.$line,array('class'=>'sourceLink','target'=>'_blank'));
		}
	}

	public function highlight($code,$limit=20)
	{
		$code=preg_replace("/^    /m",'',rtrim(str_replace("\t","    ",$code)));
		$code=highlight_string("<?php\n".$code,true);
		return preg_replace('/&lt;\\?php<br \\/>/','',$code,1);
	}

	protected function buildOfflinePages($docPath,$themePath)
	{
		$this->themePath=$themePath;
		@mkdir($docPath);
		$content=$this->render('index',null,true);
		$content=preg_replace_callback(self::URL_PATTERN,array($this,'fixOfflineLink'),$content);
		file_put_contents($docPath.'/index.html',$content);

		foreach($this->classes as $name=>$class)
		{
			$this->currentClass=$name;
			$this->pageTitle=$name.' « API « '.Yii::app()->name;
			$content=$this->render('class',array('class'=>$class),true);
			$content=preg_replace_callback(self::URL_PATTERN,array($this,'fixOfflineLink'),$content);
			file_put_contents($docPath.'/'.$name.'.html',$content);
		}
		if ($this->enableViews) {
			foreach($this->views as $path=>$view)
			{
				$this->currentView=$path;
				$this->pageTitle=$view->name;
				$content=$this->render('view',array('view'=>$view),true);
				$content=preg_replace_callback(self::URL_PATTERN,array($this,'fixOfflineLink'),$content);
				file_put_contents($docPath.'/'.$view->package.".".$view->name.'.html',$content);
			}
		}
		CFileHelper::copyDirectory($this->themePath.'/assets',$docPath,array('exclude'=>array('.svn')));

		$content=$this->renderPartial('chmProject',null,true);
		file_put_contents($docPath.'/manual.hhp',$content);

		$content=$this->renderPartial('chmIndex',null,true);
		file_put_contents($docPath.'/manual.hhk',$content);

		$content=$this->renderPartial('chmContents',null,true);
		file_put_contents($docPath.'/manual.hhc',$content);
	}

	protected function buildOnlinePages($docPath,$themePath)
	{
		$this->themePath=$themePath;
		@mkdir($docPath);
		$content=$this->renderPartial('index',null,true);
		$content=preg_replace_callback(self::URL_PATTERN,array($this,'fixOnlineLink'),$content);
		file_put_contents($docPath.'/index.html',$content);

		foreach($this->classes as $name=>$class)
		{
			$this->currentClass=$name;
			$this->pageTitle=$name;
			$content=$this->renderPartial('class',array('class'=>$class),true);
			$content=preg_replace_callback(self::URL_PATTERN,array($this,'fixOnlineLink'),$content);
			file_put_contents($docPath.'/'.$name.'.html',$content);
		}
	}

	protected function buildModel($sourcePath)
	{
		$files=CFileHelper::findFiles($sourcePath,$this->appOptions);
		foreach(CFileHelper::findFiles(YII_PATH,$this->yiiOptions) as $file) {
			$files[] = $file;
		}
		if ($this->enableViews) {
			$viewFiles = CFileHelper::findFiles($sourcePath."/views", array('fileTypes'=>array('php')));
		}
		else {
			$viewFiles = array();
		}
		$model=new DocsModel;
		$model->build($files, $viewFiles);
		return $model;
	}

	public function renderInheritance($class)
	{
		$parents=array($class->signature);
		foreach($class->parentClasses as $parent)
		{
			if(isset($this->classes[$parent]))
				$parents[]='{{'.$parent.'|'.$parent.'}}';
			else
				$parents[]=$parent;
		}
		return implode(" &raquo;\n",$parents);
	}

	public function renderImplements($class)
	{
		$interfaces=array();
		foreach($class->interfaces as $interface)
		{
			if(isset($this->classes[$interface]))
				$interfaces[]='{{'.$interface.'|'.$interface.'}}';
			else
				$interfaces[]=$interface;
		}
		return implode(', ',$interfaces);
	}

	public function renderSubclasses($class)
	{
		$subclasses=array();
		foreach($class->subclasses as $subclass)
		{
			if(isset($this->classes[$subclass]))
				$subclasses[]='{{'.$subclass.'|'.$subclass.'}}';
			else
				$subclasses[]=$subclass;
		}
		return implode(', ',$subclasses);
	}
	
	public function renderViews($class)
	{
		$views=array();
		foreach($class->views as $view)
		{
			if(isset($this->views[$view]))
				$views[]='{{'.$view.'|'.array_pop(explode(".",$view)).'}}';
			else
				$views[]=$view;
		}
		return implode(', ',$views);
	}

	public function renderTypeUrl($type)
	{
		if (stristr($type,"[]")) {
			$type = substr($type,0,-2);
			if(isset($this->classes[$type]) && $type!==$this->currentClass)
				return '{{'.$type.'|'.$type.'}}[]';
			else
				return $type."[]";
		}
		if(isset($this->classes[$type]) && $type!==$this->currentClass)
			return '{{'.$type.'|'.$type.'}}';
		else
			return $type;
	}

	public function renderSubjectUrl($type,$subject,$text=null)
	{
		if($text===null)
			$text=$subject;
		if(isset($this->classes[$type]) || isset($this->views[$type])) {
			return '{{'.$type.'::'.$subject.'-detail'.'|'.$text.'}}';
		}
		else
			return $text;
	}

	public function renderPropertySignature($property)
	{
		if(!empty($property->signature))
			return $property->signature;
		$sig='';
		if(!empty($property->getter))
			$sig=$property->getter->signature;
		if(!empty($property->setter))
		{
			if($sig!=='')
				$sig.='<br/>';
			$sig.=$property->setter->signature;
		}
		return $sig;
	}

	public function fixMethodAnchor($class,$name)
	{
		if(isset($this->classes[$class]->properties[$name]))
			return $name."()";
		else
			return $name;
	}

	protected function fixOfflineLink($matches)
	{
		if(($pos=strpos($matches[1],'::'))!==false)
		{
			$className=substr($matches[1],0,$pos);
			$method=substr($matches[1],$pos+2);
			return "<a href=\"{$className}.html#{$method}\">{$matches[2]}</a>";
		}
		else
			return "<a href=\"{$matches[1]}.html\">{$matches[2]}</a>";
	}

	protected function fixOnlineLink($matches)
	{
		if(($pos=strpos($matches[1],'::'))!==false)
		{
			$className=substr($matches[1],0,$pos);
			$method=substr($matches[1],$pos+2);
			if($className==='index')
				return "<a href=\"/doc/api/#{$method}\">{$matches[2]}</a>";
			else
				return "<a href=\"/doc/api/{$className}#{$method}\">{$matches[2]}</a>";
		}
		else
		{
			if($matches[1]==='index')
				return "<a href=\"/doc/api/\">{$matches[2]}</a>";
			else
				return "<a href=\"/doc/api/{$matches[1]}\">{$matches[2]}</a>";
		}
	}
}
