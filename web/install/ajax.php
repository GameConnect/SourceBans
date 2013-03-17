<?php
if(!isset($_POST['db'], $_POST['SBAdmin']))
  exit(json_encode(false));

$db = $_POST['db'];

// Setup config file
require 'bootstrap.php';

$config = file_get_contents(dirname(__FILE__) . '/data/config.php');
$config = str_replace('{host}',   $db['host'],   $config);
$config = str_replace('{port}',   $db['port'],   $config);
$config = str_replace('{user}',   $db['user'],   $config);
$config = str_replace('{pass}',   $db['pass'],   $config);
$config = str_replace('{name}',   $db['name'],   $config);
$config = str_replace('{prefix}', $db['prefix'], $config);
$file   = fopen($paths['config'] . '/sourcebans.php', 'w');
fwrite($file, $config);
fclose($file);

// Setup database
require WEB_ROOT . 'api.php';

$queries = file_get_contents(dirname(__FILE__) . '/data/install.sql');
$queries = str_replace('{prefix}', $db['prefix'], $queries);
foreach(explode(';', $queries) as $query)
  if(($query = trim($query)) != '')
    Yii::app()->db->createCommand($query)->execute();

// Setup web group
$group = new SBGroup;
$group->name = 'Owner';
$group->permissions = array('OWNER');
if(!$group->save())
  exit(json_encode(false));

// Setup admin
$admin = new SBAdmin;
$admin->attributes = $_POST['SBAdmin'];
$admin->group_id   = $group->id;
$admin->setPassword($admin->password);
if(!$admin->save())
  exit(json_encode(false));

exit(json_encode(true));