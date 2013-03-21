<?php
/* @var $this AdminController */
/* @var $model SettingsForm */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settings-form',
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

<fieldset>
  <legend><?php echo Yii::t('sourcebans', 'General') ?></legend>
  <div class="control-group">
    <?php echo $form->label($model,'language',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'language',array(
        'nl'=>Yii::app()->locale->getLocaleDisplayName('nl'),
        'en'=>Yii::app()->locale->getLocaleDisplayName('en'),
        'de'=>Yii::app()->locale->getLocaleDisplayName('de'),
      )); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'theme',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'theme',array(
        'bootstrap'=>'Bootstrap',
      )); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'default_page',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'default_page',array(
        'dashboard'=>Yii::t('sourcebans', 'Dashboard'),
        'bans'=>Yii::t('sourcebans', 'Bans'),
        'servers'=>Yii::t('sourcebans', 'Servers'),
        'submitban'=>Yii::t('sourcebans', 'Submit ban'),
        'protestban'=>Yii::t('sourcebans', 'Protest ban'),
      )); ?>
    </div>
  </div>

  <div class="control-group">
    <div class="controls">
      <?php $checkbox = $form->checkBox($model,'enable_submit') . $model->getAttributeLabel('enable_submit'); ?>
      <?php echo CHtml::label($checkbox,'SettingsForm_enable_submit',array('class' => 'checkbox')); ?>
      <?php $checkbox = $form->checkBox($model,'enable_protest') . $model->getAttributeLabel('enable_protest'); ?>
      <?php echo CHtml::label($checkbox,'SettingsForm_enable_protest',array('class' => 'checkbox')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'timezone',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'timezone',LocaleData::getTimezones(),array('class' => 'span6')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'date_format',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'date_format',array('size'=>60,'maxlength'=>64)); ?>
      <div class="help-inline"><?php echo CHtml::link(Yii::t('sourcebans', 'See') . ': PHP date()','http://www.php.net/date',array('target' => '_blank')); ?></div>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'password_min_length',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->numberField($model,'password_min_length',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'password_min_length'); ?>
    </div>
  </div>
</fieldset>

<fieldset>
  <legend><?php echo Yii::t('sourcebans', 'Bans') ?></legend>
  <div class="control-group">
    <?php echo $form->labelEx($model,'items_per_page',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->numberField($model,'items_per_page',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'items_per_page'); ?>
    </div>
  </div>

  <div class="control-group">
    <div class="controls">
      <?php $checkbox = $form->checkBox($model,'bans_hide_admin') . $model->getAttributeLabel('bans_hide_admin'); ?>
      <?php echo CHtml::label($checkbox,'SettingsForm_bans_hide_admin',array('class' => 'checkbox')); ?>
      <?php $checkbox = $form->checkBox($model,'bans_hide_ip') . $model->getAttributeLabel('bans_hide_ip'); ?>
      <?php echo CHtml::label($checkbox,'SettingsForm_bans_hide_ip',array('class' => 'checkbox')); ?>
      <?php $checkbox = $form->checkBox($model,'bans_public_export') . $model->getAttributeLabel('bans_public_export'); ?>
      <?php echo CHtml::label($checkbox,'SettingsForm_bans_public_export',array('class' => 'checkbox')); ?>
    </div>
  </div>
</fieldset>

<fieldset>
  <legend><?php echo Yii::t('sourcebans', 'Email') ?></legend>
  <div class="control-group">
    <div class="controls">
      <?php $checkbox = $form->checkBox($model,'enable_smtp') . $model->getAttributeLabel('enable_smtp'); ?>
      <?php echo CHtml::label($checkbox,'SettingsForm_enable_smtp',array('class' => 'checkbox')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'smtp_host',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'smtp_host',array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'smtp_port',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->numberField($model,'smtp_port',array('size'=>60,'maxlength'=>64,'placeholder'=>25)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'smtp_username',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'smtp_username',array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'smtp_password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->passwordField($model,'smtp_password',array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'smtp_secure',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'smtp_secure',array(
        'ssl'=>'SSL',
        'tls'=>'TLS',
      ),array('empty' => '- ' . Yii::t('sourcebans', 'None') . ' -')); ?>
    </div>
  </div>
</fieldset>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>

<?php Yii::app()->clientScript->registerScript('settings_form', '
  $("#SettingsForm_enable_smtp").change(function() {
    $(":input[id^=\"SettingsForm_smtp_\"]").prop("disabled", !$(this).is(":checked"));
  }).trigger("change"); 
') ?>