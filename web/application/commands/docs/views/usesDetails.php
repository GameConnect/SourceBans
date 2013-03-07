<h3>Uses Details</h3>
<?php foreach($view->uses as $property): ?>
<div class="detailHeader" id="<?php echo $property->name.'-detail'; ?>">
<?php echo $property->name; ?>
<span class="detailHeaderTag">
variable
<?php if(!empty($property->since)): ?>
 (available since v<?php echo $property->since; ?>)
<?php endif; ?>
</span>
</div>

<pre class="signature">
<?php echo $this->renderPropertySignature($property); ?>
</pre>

<p><?php echo $property->description; ?></p>

<?php $this->renderPartial('seeAlso',array('object'=>$property)); ?>

<?php endforeach; ?>
