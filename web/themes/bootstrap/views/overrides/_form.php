<?php
/* @var $this OverridesController */
/* @var $model SBOverride */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'overrides-dialog',
	'action'=>array('overrides/add'),
	//'enableAjaxValidation'=>true,
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'inputContainer'=>'.control-group',
		'validateOnSubmit'=>true,
	),
	'errorMessageCssClass'=>'help-inline',
	'htmlOptions'=>array(
		'aria-hidden'=>'true',
		'class'=>'form-horizontal modal fade hide',
		'role'=>'dialog',
	),
)) ?>

<div class="modal-header">
  <button aria-hidden="true" class="close" data-dismiss="modal" type="button">&times;</button>
  <h3><?php echo Yii::t('sourcebans', 'Override') ?></h3>
</div>
<div class="modal-body">
  <div class="control-group">
    <?php echo $form->label($model,'type',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'type',SBOverride::getTypes()); ?>
      <?php echo $form->error($model,'type'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'name',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'name'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'flags',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php $radio = CHtml::radioButton('SBServerGroup[flags]', true, array('value'=>'')) . Yii::t('sourcebans', 'Anyone'); ?>
      <?php echo CHtml::label($radio,'SBServerGroup_flags',array('class' => 'radio')); ?>
      <?php foreach(SourceBans::app()->flags as $flag => $description): ?>
        <?php if($flag == SM_ROOT) $description = Yii::t('sourcebans', 'permissions.root'); ?>
        <?php $radio = CHtml::radioButton('SBServerGroup[flags]', false, array('id'=>'SBServerGroup_flags_'.$flag,'value'=>$flag)) . $description; ?>
        <?php echo CHtml::label($radio,'SBServerGroup_flags_' . $flag,array('class' => 'radio')); ?>
      <?php endforeach ?>
      <?php echo $form->error($model,'flags'); ?>
    </div>
  </div>
</div>
<div class="modal-footer">
  <button aria-hidden="true" class="btn" data-dismiss="modal" type="button"><?php echo Yii::t('sourcebans', 'common.close') ?></button>
  <button class="btn btn-primary" disabled="disabled" type="submit"><?php echo Yii::t('sourcebans', 'Add override') ?></button>
</div>
<?php $this->endWidget() ?>
