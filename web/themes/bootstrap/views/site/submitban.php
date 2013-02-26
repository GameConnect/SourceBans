<div class="row">
  <section class="span12">
<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'submitban-form',
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
        <?php echo $form->labelEx($model,'steam',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'steam'); ?>
          <?php echo $form->error($model,'steam'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'ip',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'ip'); ?>
          <?php echo $form->error($model,'ip'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'name',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'name'); ?>
          <?php echo $form->error($model,'name'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'reason',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'reason'); ?>
          <?php echo $form->error($model,'reason'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'subname',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'subname'); ?>
          <?php echo $form->error($model,'subname'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'subemail',array('class' => 'control-label')); ?>
        <div class="controls">
          <?php echo $form->textField($model,'subemail'); ?>
          <?php echo $form->error($model,'subemail'); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo $form->labelEx($model,'server_id',array('class' => 'control-label')); ?>
        <div class="controls">
          <select class="span6" id="SBSubmission_server_id" name="SBSubmission[server_id]">
            <option value="">- <?php echo Yii::t('sourcebans', 'Unknown') ?> -</option>
<?php foreach($servers as $server): ?>
            <option value="<?php echo $server->id ?>">Querying server... (<?php echo $server->ip, ':', $server->port ?>)</option>
<?php endforeach ?>
            </select>
          <?php echo $form->error($model,'server_id',null,true,false); ?>
        </div>
      </div>

      <div class="control-group">
        <?php echo CHtml::label($model->getAttributeLabel('demo.filename'),'SBDemo_filename',array('class' => 'control-label')); ?>
        <div class="controls">
          <div class="fileupload fileupload-new" data-provides="fileupload">
            <div class="input-append">
              <div class="uneditable-input span3">
                <i class="icon-file fileupload-exists"></i>
                <span class="fileupload-preview"></span>
              </div>
              <span class="btn btn-file">
                <span class="fileupload-new"><?php echo Yii::t('sourcebans', 'Select') ?></span>
                <span class="fileupload-exists"><?php echo Yii::t('sourcebans', 'Change') ?></span>
                <?php echo CHtml::fileField('SBDemo[filename]') ?>
              </span>
              <a href="#" class="btn fileupload-exists" data-dismiss="fileupload"><?php echo Yii::t('sourcebans', 'Remove') ?></a>
            </div>
          </div>
          <?php echo $form->error($model->demo,'filename',null,true,false); ?>
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

<?php Yii::app()->clientScript->registerScript('site_submitban_queryServers', '
  $.getJSON("' . $this->createUrl('servers/info') . '", function(servers) {
    $.each(servers, function(i, server) {
      var $option = $("#SBSubmission_server_id option[value=\"" + server.id + "\"]");
      $option.text(server.error ? server.error.message : server.hostname);
    });
  });
') ?>