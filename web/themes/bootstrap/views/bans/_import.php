<?php
/* @var $this AdminController */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>array('bans/import'),
	'clientOptions'=>array(
		'inputContainer'=>'.control-group',
	),
	'errorMessageCssClass'=>'help-inline',
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
		'enctype'=>'multipart/form-data',
	),
)) ?>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'File'),'file',array('class' => 'control-label')); ?>
    <div class="controls">
      <div class="fileupload fileupload-new" data-provides="fileupload">
        <div class="input-append">
          <div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i> <span class="fileupload-preview"></span></div>
          <span class="btn btn-file">
            <span class="fileupload-new"><?php echo Yii::t('sourcebans', 'Select') ?></span>
            <span class="fileupload-exists"><?php echo Yii::t('sourcebans', 'Change') ?></span>
            <?php echo CHtml::fileField('file') ?>
          </span>
          <a href="#" class="btn fileupload-exists" data-dismiss="fileupload"><?php echo Yii::t('sourcebans', 'Remove') ?></a>
        </div>
      </div>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Import'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>