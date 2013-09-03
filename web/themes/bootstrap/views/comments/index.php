<?php
/* @var $this CommentsController */
/* @var $dataProvider CActiveDataProvider */
?>

<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'emptyText'=>Yii::t('sourcebans', 'No comments') . '.',
	'itemView'=>'_view',
	'template'=>'{items}',
)); ?>