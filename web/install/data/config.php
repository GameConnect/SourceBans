<?php

//defined('PRETTY_URLS') or define('PRETTY_URLS',true);

return array(
	// application components
	'components'=>array(
		'db'=>array(
			'connectionString'=>'mysql:host={host};port={port};dbname={name}',
			'username'=>'{user}',
			'password'=>'{pass}',
			'tablePrefix'=>'{prefix}',
		),
	),
);