<?php
/* @var $this AdminController */
/* @var $ban SBBan */
/* @var $protests SBProtest[] */
/* @var $submissions SBSubmission[] */
?>

<?php if(Yii::app()->user->data->hasPermission('ADD_BANS')): ?>
    <section class="tab-pane fade" id="pane-add">
<?php echo $this->renderPartial('/bans/_form', array(
	'action'=>array('bans/add'),
	'model'=>$ban,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-import">
<?php echo $this->renderPartial('/bans/_import') ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('BAN_PROTESTS')): ?>
    <section class="tab-pane fade" id="pane-protests">
      <ul class="menu nav nav-pills">
        <li class="active"><a href="#protests"><?php echo Yii::t('sourcebans', 'views.admin.bans.protests.active.title') ?></a></li>
        <li><a href="#protests/archive"><?php echo Yii::t('sourcebans', 'views.admin.bans.protests.archive.title') ?></a></li>
      </ul>
<?php echo $this->renderPartial('/protests/_admin', array(
	'model'=>$protests,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-protests-archive">
      <ul class="menu nav nav-pills">
        <li><a href="#protests"><?php echo Yii::t('sourcebans', 'views.admin.bans.protests.active.title') ?></a></li>
        <li class="active"><a href="#protests/archive"><?php echo Yii::t('sourcebans', 'views.admin.bans.protests.archive.title') ?></a></li>
      </ul>
<?php echo $this->renderPartial('/protests/_admin', array(
	'archive'=>true,
	'model'=>$protests,
)) ?>

    </section>
<?php endif ?>
<?php if(Yii::app()->user->data->hasPermission('BAN_SUBMISSIONS')): ?>
    <section class="tab-pane fade" id="pane-submissions">
      <ul class="menu nav nav-pills">
        <li class="active"><a href="#submissions"><?php echo Yii::t('sourcebans', 'views.admin.bans.submissions.active.title') ?></a></li>
        <li><a href="#submissions/archive"><?php echo Yii::t('sourcebans', 'views.admin.bans.submissions.archive.title') ?></a></li>
      </ul>
<?php echo $this->renderPartial('/submissions/_admin', array(
	'model'=>$submissions,
)) ?>

    </section>
    <section class="tab-pane fade" id="pane-submissions-archive">
      <ul class="menu nav nav-pills">
        <li><a href="#submissions"><?php echo Yii::t('sourcebans', 'views.admin.bans.submissions.active.title') ?></a></li>
        <li class="active"><a href="#submissions/archive"><?php echo Yii::t('sourcebans', 'views.admin.bans.submissions.archive.title') ?></a></li>
      </ul>
<?php echo $this->renderPartial('/submissions/_admin', array(
	'archive'=>true,
	'model'=>$submissions,
)) ?>

    </section>
<?php endif ?>