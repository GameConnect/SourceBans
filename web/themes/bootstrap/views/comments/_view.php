<?php
/* @var $this CommentsController */
/* @var $data SBComment */
?>

<div class="media">
  <div class="media-body">
    <em class="pull-right"><?php echo Yii::app()->format->formatDatetime($data->create_time) ?></em>
    <h4 class="media-heading"><?php echo CHtml::encode($data->admin->name) ?></h4>
    <?php echo $data->message ?>

  </div>
</div>