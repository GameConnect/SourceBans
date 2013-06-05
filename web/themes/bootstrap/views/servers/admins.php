<?php
/* @var $this ServersController */
/* @var $admins SBAdmin[] */
?>

    <section class="tab-pane" id="pane-admins">
      <table class="table table-condensed">
        <thead>
          <tr>
            <th style="width: 50%"><?php echo Yii::t('sourcebans', 'Name') ?></th>
            <th style="width: 50%"><?php echo Yii::t('sourcebans', 'Identity') ?></th>
          </tr>
        </thead>
        <tbody>
<?php foreach($admins as $admin): ?>
          <tr>
            <td><?php echo CHtml::encode($admin->name) ?></td>
            <td><?php echo CHtml::encode($admin->identity) ?></td>
          </tr>
<?php endforeach ?>
        </tbody>
      </table>
    </section>