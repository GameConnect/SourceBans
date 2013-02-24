      <section class="row">
        <div class="span12">
          <h1><?php echo Yii::t('app', 'Login'); ?></h1>
<?php $form=$this->beginWidget('CActiveForm', array(
	'id' => 'login-form',
	'enableClientValidation' => true,
	'clientOptions' => array(
		'validateOnSubmit' => true,
	),
)); ?>
            <fieldset>
              <div class="control-group">
                <?php echo $form->labelEx($model, 'username', array('class' => 'control-label')); ?>
                <?php echo $form->textField($model, 'username'); ?>
                <?php echo $form->error($model, 'username', array('class' => 'help-block')); ?>
              </div>
              <div class="control-group">
                <?php echo $form->labelEx($model, 'password', array('class' => 'control-label')); ?>
                <?php echo $form->passwordField($model, 'password'); ?>
                <?php echo $form->error($model, 'password', array('class' => 'help-block')); ?>
              </div>
              <div class="rememberMe">
                <?php echo $form->checkBox($model, 'rememberMe', array('checked' => 'checked')); ?>
                <?php echo $form->label($model, 'rememberMe', array('class' => 'control-label')); ?>
                <?php echo $form->error($model, 'rememberMe', array('class' => 'help-block')); ?>
              </div>
              <div>
                <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Login'), array('class' => 'btn btn-success')); ?>
              </div>
            </fieldset>
<?php $this->endWidget(); ?>
        </div>
      </section>