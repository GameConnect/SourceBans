<?php
/* @var $this PluginsController */
/* @var $plugin SBPlugin */
?>

<?php echo $this->renderPartial($plugin->getViewFile('settings'), array('plugin'=>$plugin)); ?>