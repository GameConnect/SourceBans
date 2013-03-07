<h1><?php echo $view->sourcePath; ?></h1>
<div id="nav">
{{index|All Packages}}
<?php if(!empty($view->uses)): ?>
| <a href="#uses">Uses</a>
<?php endif; ?>
</div>
<?php $this->renderPartial('viewSummary',array('view'=>$view)); ?>

<a name="uses"></a>
<?php $this->renderPartial('usesSummary',array('view'=>$view)); ?>
<?php $this->renderPartial('usesDetails',array('view'=>$view)); ?>