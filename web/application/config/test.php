<?php

$parent=require(dirname(__FILE__).'/main.php');

return CMap::mergeArray(
	array(
		'components'=>array(
			'fixture'=>array(
				'class'=>'system.test.CDbFixtureManager',
			),
			/* uncomment the following to provide test database connection
			'db'=>array(
				'connectionString'=>'DSN for test database',
			),
			*/
		),
	),
	$parent
);
