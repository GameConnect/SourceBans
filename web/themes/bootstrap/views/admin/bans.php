    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/bans/_form', array(
	'action'=>array('bans/add'),
	'model'=>$ban,
)) ?>

    </section>