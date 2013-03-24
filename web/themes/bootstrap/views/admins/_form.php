<?php
/* @var $this AdminsController */
/* @var $model SBAdmin */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'admin-form',
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
      <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'name'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'auth',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'auth',SBAdmin::getAuthTypes()); ?>
      <?php echo $form->error($model,'auth'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'identity',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'identity',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'identity'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'email',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'email',array('size'=>60,'maxlength'=>128)); ?>
      <?php echo $form->error($model,'email'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->passwordField($model,'password',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'password'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'server_password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'server_password',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'server_password'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'group_id',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'group_id',CHtml::listData(SBGroup::model()->findAll(array('order' => 't.name')),'id','name'),array('empty'=>'- ' . Yii::t('sourcebans','None') . ' -')); ?>
      <?php echo $form->error($model,'group_id'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'server_groups.name',array('class' => 'control-label')); ?>
    <div class="controls">
<?php $server_groups = CHtml::listData($model->server_groups, 'id', 'id') ?>
<?php foreach(SBServerGroup::model()->findAll(array('order' => 't.name')) as $server_group): ?>
      <?php $checkbox = CHtml::checkBox('SBAdmin[server_groups]['.$server_group->id.']', in_array($server_group->id, $server_groups), array('value'=>$server_group->id)) . CHtml::encode($server_group->name); ?>
      <?php echo CHtml::label($checkbox,'SBAdmin_server_groups_' . $server_group->id,array('class' => 'checkbox')); ?>
<?php endforeach ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>

<?php Yii::app()->clientScript->registerScript('auth_change', '
  $("#SBAdmin_auth").change(function() {
    if($("#SBAdmin_identity").val() == "" || $("#SBAdmin_identity").val() == "STEAM_")
      $("#SBAdmin_identity").val($(this).val() == "' . SBAdmin::STEAM_AUTH . '" ? "STEAM_" : "");
  }).trigger("change");
') ?>