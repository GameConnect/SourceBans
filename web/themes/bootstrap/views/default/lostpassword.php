<?php
/* @var $this DefaultController */
/* @var $model LostPasswordForm */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'lost-password-form',
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
    <?php echo $form->labelEx($model,'email',array('class' => 'control-label')) ?>
    <div class="controls">
      <?php echo $form->textField($model,'email') ?>
      <?php echo $form->error($model,'email') ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Submit'),array('class' => 'btn btn-warning')) ?>
    </div>
  </div>

<?php $this->endWidget() ?>