<?php
/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
return array(
	'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..',
	'messagePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messages',
	'languages'=>array('de','en','nl'),
	'fileTypes'=>array('php'),
	'overwrite'=>true,
	'sort'=>true,
	'exclude'=>array(
		'.svn',
		'.gitignore',
		'/application/commands',
		'/application/extensions',
		'/application/messages',
		'/application/modules',
		'/application/plugins',
		'/application/vendors',
		'/framework',
		'/install',
	),
);
