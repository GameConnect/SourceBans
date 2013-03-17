<?php
/* @var $this AdminController */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settings-form',
	'htmlOptions'=>array(
		'class'=>'form-horizontal',
	),
)) ?>

<fieldset>
  <legend><?php echo Yii::t('sourcebans', 'General') ?></legend>
  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'Language'),'settings_language',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::dropDownList('settings[language]',SourceBans::app()->settings->language,array(
        'en'=>Yii::app()->locale->getLocaleDisplayName('en'),
        'nl'=>Yii::app()->locale->getLocaleDisplayName('nl'),
      )); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'Theme'),'settings_theme',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::dropDownList('settings[theme]',SourceBans::app()->settings->theme,array(
        'bootstrap'=>'Bootstrap',
      )); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'Default page'),'settings_default_page',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::dropDownList('settings[default_page]',SourceBans::app()->settings->default_page,array(
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
      <?php $checkbox = CHtml::checkBox('settings[enable_submit]',SourceBans::app()->settings->enable_submit,array('uncheckValue' => 0)) . Yii::t('sourcebans', 'Enable Submit ban'); ?>
      <?php echo CHtml::label($checkbox,'settings_enable_submit',array('class' => 'checkbox')); ?>
      <?php $checkbox = CHtml::checkBox('settings[enable_protest]',SourceBans::app()->settings->enable_protest,array('uncheckValue' => 0)) . Yii::t('sourcebans', 'Enable Protest ban'); ?>
      <?php echo CHtml::label($checkbox,'settings_enable_protest',array('class' => 'checkbox')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'Timezone'),'settings_timezone',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::dropDownList('settings[timezone]',SourceBans::app()->settings->timezone,LocaleData::getTimezones(),array('class' => 'span6')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'Date format'),'settings_date_format',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::textField('settings[date_format]',SourceBans::app()->settings->date_format,array('size'=>60,'maxlength'=>64)); ?>
      <?php echo CHtml::link(Yii::t('sourcebans', 'See') . ': PHP date()','http://www.php.net/date',array('class' => 'help-inline', 'target' => '_blank')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'Min password length'),'settings_password_min_length',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::textField('settings[password_min_length]',SourceBans::app()->settings->password_min_length,array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>
</fieldset>

<fieldset>
  <legend><?php echo Yii::t('sourcebans', 'Bans') ?></legend>
  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'Items per page'),'settings_items_per_page',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::textField('settings[items_per_page]',SourceBans::app()->settings->items_per_page,array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>

  <div class="control-group">
    <div class="controls">
      <?php $checkbox = CHtml::checkBox('settings[bans_hide_admin]',SourceBans::app()->settings->bans_hide_admin,array('uncheckValue' => 0)) . Yii::t('sourcebans', 'Hide admins'); ?>
      <?php echo CHtml::label($checkbox,'settings_bans_hide_admin',array('class' => 'checkbox')); ?>
      <?php $checkbox = CHtml::checkBox('settings[bans_hide_ip]',SourceBans::app()->settings->bans_hide_ip,array('uncheckValue' => 0)) . Yii::t('sourcebans', 'Hide IP addresses'); ?>
      <?php echo CHtml::label($checkbox,'settings_bans_hide_ip',array('class' => 'checkbox')); ?>
      <?php $checkbox = CHtml::checkBox('settings[bans_public_export]',SourceBans::app()->settings->bans_public_export,array('uncheckValue' => 0)) . Yii::t('sourcebans', 'Enable public export'); ?>
      <?php echo CHtml::label($checkbox,'settings_bans_public_export',array('class' => 'checkbox')); ?>
    </div>
  </div>
</fieldset>

<fieldset>
  <legend><?php echo Yii::t('sourcebans', 'Email') ?></legend>
  <div class="control-group">
    <div class="controls">
      <?php $checkbox = CHtml::checkBox('settings[enable_smtp]',SourceBans::app()->settings->enable_smtp,array('uncheckValue' => 0)) . Yii::t('sourcebans', 'Enable SMTP'); ?>
      <?php echo CHtml::label($checkbox,'settings_enable_smtp',array('class' => 'checkbox')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'SMTP host'),'settings_smtp_host',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::textField('settings[smtp_host]',SourceBans::app()->settings->smtp_host,array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'SMTP port'),'settings_smtp_port',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::textField('settings[smtp_port]',SourceBans::app()->settings->smtp_port,array('size'=>60,'maxlength'=>64,'placeholder'=>25)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'SMTP username'),'settings_smtp_username',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::textField('settings[smtp_username]',SourceBans::app()->settings->smtp_username,array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'SMTP password'),'settings_smtp_password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::textField('settings[smtp_password]',SourceBans::app()->settings->smtp_password,array('size'=>60,'maxlength'=>64)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo CHtml::label(Yii::t('sourcebans', 'SMTP security'),'settings_smtp_secure',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo CHtml::dropDownList('settings[smtp_secure]',SourceBans::app()->settings->smtp_secure,array(
        ''=>'- ' . Yii::t('sourcebans', 'None') . ' -',
        'ssl'=>'SSL',
        'tls'=>'TLS',
      )); ?>
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
  $("#settings_enable_smtp").change(function() {
    $(":input[id^=\"settings_smtp_\"]").prop("disabled", !$(this).is(":checked"))
  }).trigger("change"); 
') ?>