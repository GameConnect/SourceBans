<?php
/* @var $this BansController */
/* @var $model SBBan */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'ban-form',
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
      <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'name'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'type',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'type',SBBan::getTypes()); ?>
      <?php echo $form->error($model,'type'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'steam',array('class' => 'control-label', 'required' => true)); ?>
    <div class="controls">
      <?php echo $form->textField($model,'steam',array('size'=>32,'maxlength'=>32)); ?>
      <?php echo $form->error($model,'steam'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'ip',array('class' => 'control-label', 'required' => true)); ?>
    <div class="controls">
      <?php echo $form->textField($model,'ip',array('size'=>15,'maxlength'=>15)); ?>
      <?php echo $form->error($model,'ip'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'reason',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textArea($model,'reason',array('size'=>60,'maxlength'=>255)); ?>
      <?php echo $form->error($model,'reason'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'length',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'length',SBBan::getTimes()); ?>
      <?php echo $form->error($model,'length'); ?>
    </div>
  </div>

<?php if($model->isNewRecord): ?>
  <div class="control-group">
    <?php echo CHtml::label($model->getAttributeLabel('demo.filename'),'SBDemo_filename',array('class' => 'control-label')); ?>
    <div class="controls">
      <div class="fileupload fileupload-new" data-provides="fileupload">
        <div class="input-append">
          <div class="uneditable-input span3"><i class="icon-file fileupload-exists"></i> <span class="fileupload-preview"></span></div>
          <span class="btn btn-file">
            <span class="fileupload-new"><?php echo Yii::t('sourcebans', 'Select') ?></span>
            <span class="fileupload-exists"><?php echo Yii::t('sourcebans', 'Change') ?></span>
            <?php echo $form->fileField($demo,'filename') ?>
          </span>
          <a href="#" class="btn fileupload-exists" data-dismiss="fileupload"><?php echo Yii::t('sourcebans', 'Remove') ?></a>
        </div>
      </div>
      <?php echo $form->error($demo,'filename',null,true,false); ?>
    </div>
  </div>
<?php endif ?>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>

<?php Yii::app()->clientScript->registerScript('type_change', '
  $("#SBBan_type").change(function() {
    if($("#SBBan_steam").val() == "" || $("#SBBan_steam").val() == "STEAM_")
      $("#SBBan_steam").val($(this).val() == ' . SBBan::STEAM_TYPE . ' ? "STEAM_" : "");
    
    $("label[for=\"SBBan_steam\"] .required").toggle($(this).val() == ' . SBBan::STEAM_TYPE . ');
    $("label[for=\"SBBan_ip\"] .required").toggle($(this).val() == ' . SBBan::IP_TYPE . ');
  }).trigger("change");
') ?>