<?php
/* @var $this BansController */
/* @var $model SBBan */

$this->pageTitle=Yii::t('sourcebans', 'Bans');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Bans') => array('admin/bans'),
	$model->name,
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('site/bans')),
);
?>

    <section class="tab-pane" id="pane-edit">
<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>

    </section>