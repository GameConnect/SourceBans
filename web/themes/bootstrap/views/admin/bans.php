<?php
/* @var $this AdminController */
/* @var $ban SBBan */
?>

<?php if(Yii::app()->user->data->hasPermission('ADD_BANS')): ?>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/bans/_form', array(
	'action'=>array('bans/add'),
	'model'=>$ban,
)) ?>

    </section>
<?php endif ?>