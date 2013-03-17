<?php
/* @var $this GamesController */
/* @var $model SBGame */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'game-form',
	'action'=>isset($action) ? $action : null,
	'enableAjaxValidation'=>true,
	'enableClientValidation'=>true,
	'clientOptions'=>array(
		'inputContainer'=>'.control-group',
		'validateOnSubmit'=>true,
	),
	'errorMessageCssClass'=>'help-inline',
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
		'enctype'=>'multipart/form-data',
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
    <?php echo $form->labelEx($model,'folder',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'folder',array('size'=>32,'maxlength'=>32)); ?>
      <?php echo $form->error($model,'folder'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'icon',array('class' => 'control-label')); ?>
    <div class="controls">
      <div class="fileupload fileupload-<?php if($model->icon): ?>exists<?php else: ?>new<?php endif ?>" data-provides="fileupload">
        <div class="input-append">
          <div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i> <span class="fileupload-preview"><?php echo $model->icon ?></span></div>
          <span class="btn btn-file">
            <span class="fileupload-new"><?php echo Yii::t('sourcebans', 'Select') ?></span>
            <span class="fileupload-exists"><?php echo Yii::t('sourcebans', 'Change') ?></span>
            <?php echo $form->fileField($model,'icon'); ?>
          </span>
          <a href="#" class="btn fileupload-exists" data-dismiss="fileupload"><?php echo Yii::t('sourcebans', 'Remove') ?></a>
        </div>
      </div>
      <?php echo $form->error($model,'icon'); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>