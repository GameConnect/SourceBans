<?php
/* @var $this ServersController */

preg_match_all('/([a-z_]+)=([^;]+)/i', Yii::app()->db->connectionString, $dsn);
$db = array_combine($dsn[1], $dsn[2]);
?>

    <section class="tab-pane" id="pane-config">
      <p><?php echo Yii::t('sourcebans', 'The game server needs the following configuration to be able to communicate with the database.') ?></p>
      <p><?php echo Yii::t('sourcebans', 'Copy the {section} section below and place it on the game server in {file}, so that it looks similar to this:', array(
        '{file}' => '<strong>/[' . strtolower(Yii::t('sourcebans', 'Game')) . ']/addons/sourcemod/configs/databases.cfg</strong>',
        '{section}' => '<strong>"sourcebans"</strong>',
      )) ?></p>
      <textarea class="span9" cols="60" rows="20" readonly="readonly">"Databases"
{
	"driver_default"		"mysql"
	
	"sourcebans"
	{
		"driver"			"mysql"
		"host"			"<?php echo $db['host'] == 'localhost' ? Yii::app()->request->serverHostAddress : $db['host'] ?>"
		"database"		"<?php echo $db['dbname'] ?>"
		"user"			"<?php echo Yii::app()->db->username ?>"
		"pass"			"<?php echo Yii::app()->db->password ?>"
		//"timeout"		"0"
		"port"			"<?php echo $db['port'] ?>"
	}
	
	"storage-local"
	{
		"driver"			"sqlite"
		"database"		"sourcemod-local"
	}
}</textarea>
    </section>