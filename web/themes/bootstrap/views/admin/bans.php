<?php
/* @var $this AdminController */
/* @var $ban SBBan */

$this->pageTitle=Yii::t('sourcebans', 'Bans');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Administration') => array('admin/index'),
	Yii::t('sourcebans', 'Bans'),
);

$this->menu=array(
	array('label'=>Yii::t('sourcebans', 'Add ban'), 'url'=>'#add', 'visible'=>Yii::app()->user->data->hasPermission('ADD_BANS')),
);
?>

<?php if(Yii::app()->user->data->hasPermission('ADD_BANS')): ?>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/bans/_form', array(
	'action'=>array('bans/add'),
	'model'=>$ban,
)) ?>

    </section>
<?php endif ?>