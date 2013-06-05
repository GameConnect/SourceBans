<?php
/* @var $this GamesController */
/* @var $model MapImageForm */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'map-image-form',
	'action'=>array('games/mapImage'),
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
    <?php echo $form->label($model,'game_id',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'game_id',CHtml::listData(SBGame::model()->findAll(array('order' => 't.name')), 'id', 'name')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'filename',array('class' => 'control-label')); ?>
    <div class="controls">
      <div class="fileupload fileupload-<?php if($model->filename): ?>exists<?php else: ?>new<?php endif ?>" data-provides="fileupload">
        <div class="input-append">
          <div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i> <span class="fileupload-preview"><?php echo $model->filename ?></span></div>
          <span class="btn btn-file">
            <span class="fileupload-new"><?php echo Yii::t('sourcebans', 'Select') ?></span>
            <span class="fileupload-exists"><?php echo Yii::t('sourcebans', 'Change') ?></span>
            <?php echo $form->fileField($model,'filename'); ?>
          </span>
          <a href="#" class="btn fileupload-exists" data-dismiss="fileupload"><?php echo Yii::t('sourcebans', 'Remove') ?></a>
        </div>
      </div>
      <?php echo $form->error($model,'filename'); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>