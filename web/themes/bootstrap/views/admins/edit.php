<?php
/* @var $this AdminsController */
/* @var $model SBAdmin */

$this->pageTitle=Yii::t('sourcebans', 'Admins');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Admins') => array('admin/admins'),
	$model->name,
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/admins')),
);
?>

    <section class="tab-pane" id="pane-edit">
<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>

    </section>