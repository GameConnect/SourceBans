<?php
/* @var $this GamesController */
/* @var $model SBGame */

$this->pageTitle=Yii::t('sourcebans', 'Games');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Games') => array('admin/games'),
	$model->name,
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'Back'), 'url'=>array('admin/games')),
);
?>

    <section class="tab-pane" id="pane-edit">
<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>

    </section>