<?php
/* @var $this ServersController */
/* @var $model SBServer */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'server-form',
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
    <?php echo $form->labelEx($model,'ip',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'ip',array('size'=>15,'maxlength'=>15)); ?>
      <?php echo $form->error($model,'ip'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'port',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'port',array('placeholder'=>27015)); ?>
      <?php echo $form->error($model,'port'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'rcon',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'rcon',array('size'=>32,'maxlength'=>32)); ?>
      <?php echo $form->error($model,'rcon'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'game_id',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'game_id',CHtml::listData(SBGame::model()->findAll(array('order' => 't.name')), 'id', 'name')); ?>
    </div>
  </div>

  <div class="control-group">
    <div class="controls">
      <?php $enabled = $form->checkBox($model,'enabled') . $model->getAttributeLabel('enabled'); ?>
      <?php echo CHtml::label($enabled,'SBServer_enabled',array('class' => 'checkbox')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'groups.name',array('class' => 'control-label')); ?>
    <div class="controls">
<?php $groups = CHtml::listData($model->groups, 'id', 'id') ?>
<?php foreach(SBServerGroup::model()->findAll(array('order' => 't.name')) as $server_group): ?>
      <?php $checkbox = CHtml::checkBox('SBServer[groups]['.$server_group->id.']', in_array($server_group->id, $groups), array('value'=>$server_group->id)) . CHtml::encode($server_group->name); ?>
      <?php echo CHtml::label($checkbox,'SBServer_groups_' . $server_group->id,array('class' => 'checkbox')); ?>
<?php endforeach ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>