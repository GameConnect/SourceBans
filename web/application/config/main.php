<?php

$parent=require(dirname(__FILE__).'/sourcebans.php');

return CMap::mergeArray(
	array(
		'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
		'name'=>'SourceBans',
		'language'=>'en',
		'sourceLanguage'=>'00',
		'timeZone'=>'Europe/London',
		
		// preloading 'log' component
		'preload'=>array('log'),
		
		// autoloading model and component classes
		'import'=>array(
			'application.models.*',
			'application.components.*',
			'application.utils.*',
		),
		
		// application components
		'components'=>array(
			'cache'=>array(
				'class'=>'CFileCache',
			),
			'db'=>array(
				'emulatePrepare'=>true,
				'charset'=>'utf8',
				'autoConnect'=>false,
				'enableProfiling'=>YII_DEBUG,
				'schemaCachingDuration'=>86400,
			),
			'format'=>array(
				'class'=>'Formatter',
				'datetimeFormat'=>'m-d-y H:i',
			),
			'log'=>array(
				'class'=>'CLogRouter',
				'routes'=>array(
					array(
						'class'=>'CFileLogRoute',
						'levels'=>'error, warning',
					),
				),
			),
			'request'=>array(
				'class'=>'HttpRequest',
			),
			// extensions
			'geoip'=>array(
				'class'=>'ext.geoip.CGeoIP',
				'filename'=>dirname(__FILE__).'/../extensions/geoip/data/GeoIP.dat',
			),
			'mailer'=>array(
				'class'=>'ext.swiftMailer.SwiftMailer',
			),
		),
	),
	$parent
);