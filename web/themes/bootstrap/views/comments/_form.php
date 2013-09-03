<?php
/* @var $this CommentsController */
/* @var $model SBComment */
/* @var $form CActiveForm */
?>

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'comment-form',
	'action'=>array('comments/add'),
)) ?>

  <div class="control-group">
<?php $this->widget('ext.tinymce.ETinyMce', array(
	'model'=>$model,
	'attribute'=>'message',
	'contentCSS'=>implode(',', array(
		Yii::app()->assetManager->getPublishedUrl(Yii::getPathOfAlias('bootstrap.assets'), true) . '/css/bootstrap.min.css',
		Yii::app()->assetManager->getPublishedUrl(Yii::getPathOfAlias('bootstrap.assets'), true) . '/css/yii.css',
		Yii::app()->theme->baseUrl . '/css/style.css',
		Yii::app()->theme->baseUrl . '/css/tinymce.css',
	)),
	'editorTemplate'=>'full',
	'height'=>'150px',
	'options'=>array(
		'document_base_url'=>Yii::app()->request->hostInfo.Yii::app()->baseUrl.'/',
		'plugins'=>'advhr,advimage,advlink,directionality,emotions,inlinepopups,media,nonbreaking,noneditable,paste,xhtmlxtras',
		'schema'=>'html5',
		'setup'=> 'js:function(ed) {
			var toggleSubmit = function(ed) {
				$("#comments-dialog .modal-footer :submit").attr("disabled", !ed.getContent().length);
			};
			
			ed.onKeyUp.add(toggleSubmit);
			ed.onRedo.add(toggleSubmit);
			ed.onUndo.add(toggleSubmit);
		}',
		'theme_advanced_buttons1'=>'bold,italic,underline,strikethrough,sub,sup,|,justifyleft,justifycenter,justifyright,justifyfull,|,forecolor,backcolor,styleprops,removeformat,|,advhr,charmap,emotions,image,media',
		'theme_advanced_buttons2'=>'cut,copy,paste,|,undo,redo,|,bullist,numlist,outdent,indent,|,link,unlink,cite,abbr,acronym,ins,del',
		'theme_advanced_buttons3'=>'',
		'theme_advanced_buttons4'=>'',
		'theme_advanced_path'=>false,
		'theme_advanced_path_location'=>'none',
		'theme_advanced_resizing'=>false,
		'theme_advanced_statusbar_location'=>'none',
	),
	'useSwitch'=>false,
	'width'=>'530px',
)) ?>

  </div>

  <div class="control-group buttons">
<?php echo $form->hiddenField($model, 'object_type') ?>
<?php echo $form->hiddenField($model, 'object_id') ?>

    <button aria-hidden="true" class="btn" data-dismiss="modal" type="button"><?php echo Yii::t('sourcebans', 'Close') ?></button>
    <button class="btn btn-primary" disabled="disabled" type="submit"><?php echo Yii::t('sourcebans', 'Add comment') ?></button>
  </div>

<?php $this->endWidget() ?>