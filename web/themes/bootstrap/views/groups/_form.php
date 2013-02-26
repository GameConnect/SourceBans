<?php
/* @var $this GroupsController */
/* @var $model SBServerGroup */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'servergroup-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'inputContainer'=>'.control-group',
		'validateOnSubmit'=>true,
	),
	'errorMessageCssClass'=>'help-inline',
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
	),
)) ?>

  <div class="control-group">
    <?php echo $form->labelEx($model,'name',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'name',array('size'=>32,'maxlength'=>32)); ?>
      <?php echo $form->error($model,'name'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'flags',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'flags',array('size'=>32,'maxlength'=>32)); ?>
      <?php echo $form->error($model,'flags'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'immunity',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->numberField($model,'immunity'); ?>
      <?php echo $form->error($model,'immunity'); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>