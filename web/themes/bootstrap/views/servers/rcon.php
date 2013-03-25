<?php
/* @var $this ServersController */
/* @var $model SBServer */
?>

    <section class="tab-pane" id="pane-rcon">
      <pre class="pre-scrollable" id="console">



















********************************************************
**                                                    **
* SourceBans <?php echo str_pad(Yii::t('sourcebans', 'controllers.servers.rcon.title'), 41) ?> *
* <?php echo str_pad(Yii::t('sourcebans', 'Type \'clr\' to clear the console'), 52) ?> *
**                                                    **
********************************************************

</pre>
      <form class="form-inline" id="command-form" method="post">
        <div class="input-append">
          <?php echo CHtml::textField('command', null, array('placeholder'=>Yii::t('sourcebans', 'Command'))) ?>
          <?php echo CHtml::submitButton(Yii::t('sourcebans', 'Execute'), array('class'=>'btn btn-primary', 'disabled'=>true, 'id'=>'submit')) ?>
        </div>
      </form>
    </section>

<?php Yii::app()->clientScript->registerCoreScript('jquery.ui') ?>
<?php Yii::app()->clientScript->registerScript('rcon', '
  function scrollConsole(duration) {
    var $console = $("#console");
    $console.stop(true).animate({
      scrollTop: $console[0].scrollHeight - $console.height()
    }, duration, "easeInOutCubic");
  }
  
  $("#command-form").submit(function(e) {
    e.preventDefault();
    $("#submit").prop("disabled", true);
    
    var $console = $("#console");
    var $command = $("#command");
    var command  = $command.val().trim();
    if(command == "")
      return;
    if(command == "clr") {
      $console.text("");
      $command.val("");
    }
    else {
      $command.prop("disabled", true);
      $console.text($console.text() + "> " + command + "\n");
      scrollConsole(200);
      
      $.post("' . $this->createUrl('', array('id' => $model->id)) . '", {
        command: command
      }, function(data) {
        $console.text($console.text() + (data || "' . Yii::t('sourcebans', 'Command executed') . '") + "\n");
        scrollConsole(200);
        
        $command.val("").prop("disabled", false);
      });
    }
  });
  $("#command").keyup(function(e) {
    // If Enter was pressed, ignore
    if(e.which == 13)
      return;
    
    $("#submit").prop("disabled", $(this).val().trim() == "");
  });
  
  $(function() {
    scrollConsole(800);
  });
') ?>