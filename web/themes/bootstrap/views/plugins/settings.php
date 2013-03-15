<?php
/* @var $this PluginsController */
/* @var $plugin SBPlugin */

$this->pageTitle=$plugin->name;

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Plugins') => array('admin/settings', '#'=>'plugins'),
	$plugin->name,
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/settings','#'=>'plugins')),
);
?>

<?php echo $this->renderPartial($plugin->getViewFile('settings'), array('plugin'=>$plugin)); ?>