<?php
/* @var $this SiteController */
/* @var $model SBAppeal */
?>
<div class="row">
  <section class="span12">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'appeal-form',
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
        <?php echo $form->labelEx($model,'ban_steam',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'ban_steam'); ?>
          <?php echo $form->error($model,'ban_steam'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'ban_ip',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'ban_ip'); ?>
          <?php echo $form->error($model,'ban_ip'); ?>
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