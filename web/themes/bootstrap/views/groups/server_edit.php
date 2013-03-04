<?php
/* @var $this GroupsController */
/* @var $model SBServerGroup */

$this->pageTitle=Yii::t('sourcebans', 'Groups');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Groups') => array('admin/groups'),
	$model->name,
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/groups')),
);
?>

<?php echo $this->renderPartial('_server_form', array('model'=>$model)); ?>