<?php
/* @var $this GroupsController */
/* @var $model SBGroup */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'group-form',
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

  <div class="permissions control-group">
    <?php echo $form->label($model,'permissions',array('class' => 'control-label')); ?>
    <div class="controls">
<?php $permissions = CHtml::listData($model->permissions, 'name', 'name') ?>
<?php foreach(SourceBans::app()->permissions as $name => $description): ?>
      <?php $checkbox = CHtml::checkBox('SBGroupPermission['.$name.']', in_array($name, $permissions)) . $description; ?>
      <?php echo CHtml::label($checkbox,'SBGroupPermission_' . $name,array('class' => 'checkbox')); ?>
<?php endforeach ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>

<?php Yii::app()->clientScript->registerScript('permissions_change', '
  $("#SBGroupPermission_OWNER").change(function() {
    $(".permissions :checkbox").prop("checked", $(this).is(":checked")); 
  });
  $(".permissions :checkbox").change(function() {
    $("#SBGroupPermission_OWNER").prop("checked", $(".permissions :checkbox").not("#SBGroupPermission_OWNER").are(":checked"));
  });
  
  if($("#SBGroupPermission_OWNER").is(":checked")) {
    $("#SBGroupPermission_OWNER").trigger("change");
  }
') ?>