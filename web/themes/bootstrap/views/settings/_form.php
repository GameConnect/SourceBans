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
        'dashboard'=>Yii::t('sourcebans', 'controllers.site.dashboard.title'),
        'bans'=>Yii::t('sourcebans', 'controllers.site.bans.title'),
        'servers'=>Yii::t('sourcebans', 'controllers.site.servers.title'),
        'submitban'=>Yii::t('sourcebans', 'controllers.site.submitban.title'),
        'protestban'=>Yii::t('sourcebans', 'controllers.site.protestban.title'),
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
  <legend><?php echo Yii::t('sourcebans', 'controllers.site.dashboard.title') ?></legend>
  <div class="control-group">
    <div class="controls">
      <?php $checkbox = $form->checkBox($model,'dashboard_blocks_popup') . $model->getAttributeLabel('dashboard_blocks_popup'); ?>
      <?php echo CHtml::label($checkbox,'SettingsForm_dashboard_blocks_popup',array('class' => 'checkbox')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'dashboard_title',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'dashboard_title',array('class'=>'span6','size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'dashboard_title'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php $this->widget('ext.tinymce.ETinyMce', array(
      'model'=>$model,
      'attribute'=>'dashboard_text',
      'contentCSS'=>implode(',', array(
        Yii::app()->assetManager->getPublishedUrl(Yii::getPathOfAlias('bootstrap.assets'), true) . '/css/bootstrap.min.css',
        Yii::app()->assetManager->getPublishedUrl(Yii::getPathOfAlias('bootstrap.assets'), true) . '/css/yii.css',
        Yii::app()->theme->baseUrl . '/css/style.css',
        Yii::app()->theme->baseUrl . '/css/tinymce.css',
      )),
      'editorTemplate'=>'full',
      'height'=>'300px',
      'options'=>array(
        'document_base_url'=>Yii::app()->request->hostInfo.Yii::app()->baseUrl.'/',
        'plugins'=>'advhr,advimage,advlink,contextmenu,directionality,emotions,inlinepopups,media,nonbreaking,noneditable,paste,style,table,xhtmlxtras',
        'schema'=>'html5',
        'theme_advanced_buttons1'=>'bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,formatselect,styleselect,styleprops,removeformat,|,advhr,charmap,emotions,image,media,|,code',
        'theme_advanced_buttons2'=>'cut,copy,paste,|,undo,redo,|,bullist,numlist,outdent,indent,|,link,unlink,cite,abbr,acronym,ins,del,|,tablecontrols',
        'theme_advanced_buttons3'=>'',
        'theme_advanced_buttons4'=>'',
        'theme_advanced_resize_horizontal'=>false,
        'theme_advanced_resizing_min_height'=>300,
      ),
      'useSwitch'=>false,
      'width'=>'100%',
    )); ?>
  </div>
</fieldset>

<fieldset>
  <legend><?php echo Yii::t('sourcebans', 'controllers.site.bans.title') ?></legend>
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