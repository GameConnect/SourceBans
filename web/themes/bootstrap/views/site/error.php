<?php
/* @var $this SiteController */
/* @var $code integer */
/* @var $type string */
/* @var $errorCode integer */
/* @var $message string */
/* @var $file string */
/* @var $line integer */
/* @var $trace string */
/* @var $traces array */

$this->pageTitle=Yii::t('sourcebans', 'Error');

$this->breadcrumbs=array(
	Yii::t('sourcebans', 'Error'),
);
?>
<div class="alert alert-block alert-error">
  <h4><?php echo $code ?></h4>
  <?php echo $message ?>
</div>