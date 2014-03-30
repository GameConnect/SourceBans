<?php
/* @var $this SiteController */
/* @var $model SBBan */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
	'clientOptions'=>array(
		'inputContainer'=>'.control-group',
	),
	'errorMessageCssClass'=>'help-inline',
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
	),
)) ?>

  <div class="control-group">
    <?php echo $form->label($model,'name',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'steam',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'steam',array('size'=>32,'maxlength'=>32)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'ip',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'ip',array('size'=>15,'maxlength'=>15)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'reason',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'reason',array('size'=>60,'maxlength'=>255)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'create_time',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'create_time'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'length',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'length',SBBan::getTimes(),array('empty' => '- ' . Yii::t('sourcebans', 'None') . ' -')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'admin_id',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'admin_id',CHtml::listData(SBAdmin::model()->findAll(array('order' => 'name')), 'id', 'name'),array('empty' => '- ' . Yii::t('sourcebans', 'None') . ' -')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'server_id',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'server_id',CHtml::listData(SBServer::model()->enabled()->with('game')->findAll(array('order' => 'game.name, t.host, t.port')), 'id', 'address', 'game.name'),array('empty' => '- ' . Yii::t('sourcebans', 'None') . ' -')); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Search'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>