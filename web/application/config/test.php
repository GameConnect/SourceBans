<?php

$parent=require(dirname(__FILE__).'/main.php');

return CMap::mergeArray(
	$parent,
	array(
		'components'=>array(
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			'db'=>array(
				'connectionString'=>'mysql:host=localhost;port=3306;dbname=sourcebans_test',
				'username'=>'root',
				'password'=>'',
				'tablePrefix'=>'',
			),
		),
	)
);
