<?php
/* @var $this ServersController */
/* @var $model SBServer */

$this->pageTitle=Yii::t('sourcebans', 'Servers');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Servers') => array('admin/servers'),
	$model->address,
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/servers')),
);
?>

    <section class="tab-pane" id="pane-edit">
<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>

    </section>