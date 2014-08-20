<?php
/* @var $this DefaultController */
/* @var $model AccountForm */
/* @var $form CActiveForm */
?>
    <section class="tab-pane fade" id="pane-permissions">
      <div class="row-fluid">
        <div class="span6">
          <h3><?php echo Yii::app()->user->data->getAttributeLabel('flags') ?></h3>
          <ul>
<?php foreach(SourceBans::app()->flags as $flag => $description): ?>
<?php if($flag != SM_ROOT && Yii::app()->user->data->hasFlag($flag)): ?>
            <li><?php echo CHtml::encode($description) ?></li>
<?php endif ?>
<?php endforeach ?>
          </ul>
        </div>
        <div class="span6">
          <h3><?php echo Yii::t('sourcebans', 'Web permissions') ?></h3>
          <ul>
<?php foreach(SourceBans::app()->permissions as $name => $description): ?>
<?php if($name != 'OWNER' && Yii::app()->user->data->hasPermission($name)): ?>
            <li><?php echo CHtml::encode($description) ?></li>
<?php endif ?>
<?php endforeach ?>
          </ul>
        </div>
      </div>

    </section>
    <section class="tab-pane fade" id="pane-settings">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'settings-form',
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
<?php $model->setScenario('settings') ?>

  <div class="control-group">
    <?php echo $form->label($model,'language',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'language',SourceBans::app()->languages,array('empty' => '- ' . Yii::t('sourcebans', 'Default setting') . ' -')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'theme',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'theme',SourceBans::app()->themes,array('empty' => '- ' . Yii::t('sourcebans', 'Default setting') . ' -')); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->label($model,'timezone',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->dropDownList($model,'timezone',LocaleData::getTimezones(),array('class' => 'span6','empty' => '- ' . Yii::t('sourcebans', 'Default setting') . ' -')); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::hiddenField('scenario',$model->scenario); ?>
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>
    </section>
    <section class="tab-pane fade" id="pane-email">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'email-form',
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
<?php $model->setScenario('email') ?>

  <div class="control-group">
    <?php echo $form->label($model,'email',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'email',array('size'=>60,'maxlength'=>64,'readonly'=>true)); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'new_email',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'new_email',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'new_email'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'confirm_email',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->textField($model,'confirm_email',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'confirm_email'); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::hiddenField('scenario',$model->scenario); ?>
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>
    </section>
    <section class="tab-pane fade" id="pane-password">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'password-form',
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
<?php $model->setScenario('password') ?>

  <div class="control-group">
    <?php echo $form->labelEx($model,'current_password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->passwordField($model,'current_password',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'current_password'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'new_password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->passwordField($model,'new_password',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'new_password'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'confirm_password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->passwordField($model,'confirm_password',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'confirm_password'); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::hiddenField('scenario',$model->scenario); ?>
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>
    </section>
    <section class="tab-pane fade" id="pane-server-password">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'server-password-form',
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
<?php $model->setScenario('server_password') ?>

  <div class="control-group">
    <?php echo $form->labelEx($model,'new_server_password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->passwordField($model,'new_server_password',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'new_server_password'); ?>
    </div>
  </div>

  <div class="control-group">
    <?php echo $form->labelEx($model,'confirm_server_password',array('class' => 'control-label')); ?>
    <div class="controls">
      <?php echo $form->passwordField($model,'confirm_server_password',array('size'=>60,'maxlength'=>64)); ?>
      <?php echo $form->error($model,'confirm_server_password'); ?>
    </div>
  </div>

  <div class="control-group buttons">
    <div class="controls">
      <?php echo CHtml::hiddenField('scenario',$model->scenario); ?>
      <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Save'),array('class' => 'btn')); ?>
    </div>
  </div>

<?php $this->endWidget() ?>
    </section>