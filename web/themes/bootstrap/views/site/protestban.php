<?php
/* @var $this SiteController */
/* @var $model SBProtest */
?>
<div class="row">
  <section class="span12">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'protestban-form',
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
        <?php echo $form->labelEx($model->ban,'steam',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo CHtml::textField('SBBan[steam]'); ?>
          <?php echo $form->error($model->ban,'steam'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model->ban,'ip',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo CHtml::textField('SBBan[ip]',Yii::app()->request->userHostAddress); ?>
          <?php echo $form->error($model->ban,'ip'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'reason',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textArea($model,'reason'); ?>
          <?php echo $form->error($model,'reason'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'user_email',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'user_email'); ?>
          <?php echo $form->error($model,'user_email'); ?>
        </div>
      </div>

      <div class="control-group buttons">
        <div class="controls">
          <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Submit'),array('class' => 'btn')); ?>
        </div>
      </div>

<?php $this->endWidget() ?>
  </section>
</div>