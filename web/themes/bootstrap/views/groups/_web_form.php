<?php
/* @var $this GroupsController */
/* @var $model SBGroup */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'group-form',
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
      <?php $checkbox = CHtml::checkBox('SBGroup[permissions]['.$name.']', in_array($name, $permissions), array('value'=>$name)) . $description; ?>
      <?php echo CHtml::label($checkbox,'SBGroup_permissions_' . $name,array('class' => 'checkbox')); ?>
<?php endforeach ?>
      <?php echo $form->error($model,'permissions'); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>

<?php Yii::app()->clientScript->registerScript('permissions_change', '
  $("#SBGroup_permissions_OWNER").change(function() {
    $(".permissions :checkbox").prop("checked", $(this).is(":checked")); 
  });
  $(".permissions :checkbox").change(function() {
    $("#SBGroup_permissions_OWNER").prop("checked", $(".permissions :checkbox").not("#SBGroup_permissions_OWNER").are(":checked"));
  });
  
  if($("#SBGroup_permissions_OWNER").is(":checked")) {
    $("#SBGroup_permissions_OWNER").trigger("change");
  }
') ?>